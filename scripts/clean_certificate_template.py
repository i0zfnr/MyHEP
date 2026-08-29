#!/usr/bin/env python3
"""Create a single-page clean certificate master by inpainting recipient text."""

from __future__ import annotations

import argparse
import json
from pathlib import Path

import cv2
import fitz
import numpy as np


def mm_to_px(value: float, dpi: int) -> int:
    return int(round(value * dpi / 25.4))


def clean_region(image: np.ndarray, region: dict, dpi: int) -> int:
    height, width = image.shape[:2]
    x1 = max(0, min(width - 1, mm_to_px(float(region["x_mm"]), dpi)))
    y1 = max(0, min(height - 1, mm_to_px(float(region["y_mm"]), dpi)))
    x2 = max(x1 + 1, min(width, mm_to_px(float(region["x_mm"]) + float(region["width_mm"]), dpi)))
    y2 = max(y1 + 1, min(height, mm_to_px(float(region["y_mm"]) + float(region["height_mm"]), dpi)))

    crop = image[y1:y2, x1:x2]
    gray = cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY)
    threshold = min(190, int(np.percentile(gray, 25)) + 25)
    local_mask = np.where(gray < threshold, 255, 0).astype(np.uint8)

    kernel_size = max(3, int(round(dpi / 100)))
    if kernel_size % 2 == 0:
        kernel_size += 1
    kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (kernel_size, kernel_size))
    local_mask = cv2.morphologyEx(local_mask, cv2.MORPH_CLOSE, kernel)
    local_mask = cv2.dilate(local_mask, kernel, iterations=1)

    masked_pixels = int(cv2.countNonZero(local_mask))
    if masked_pixels < 10:
        raise ValueError("No removable dark placeholder text was found inside an approved region.")

    mask = np.zeros((height, width), dtype=np.uint8)
    mask[y1:y2, x1:x2] = local_mask
    radius = max(3, int(round(dpi / 45)))
    image[:] = cv2.inpaint(image, mask, radius, cv2.INPAINT_TELEA)

    return masked_pixels


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("input_pdf", type=Path)
    parser.add_argument("output_pdf", type=Path)
    parser.add_argument("page", type=int)
    parser.add_argument("regions_json")
    parser.add_argument("--dpi", type=int, default=300)
    args = parser.parse_args()

    regions = json.loads(args.regions_json)
    if not isinstance(regions, list) or len(regions) != 2:
        raise ValueError("Exactly two approved cleaning regions are required.")

    source = fitz.open(args.input_pdf)
    page_index = args.page - 1
    if page_index < 0 or page_index >= source.page_count:
        raise ValueError("Selected certificate page does not exist.")

    page = source[page_index]
    page_rect = page.rect
    scale = args.dpi / 72
    pixmap = page.get_pixmap(matrix=fitz.Matrix(scale, scale), alpha=False)
    rgb = np.frombuffer(pixmap.samples, dtype=np.uint8).reshape(pixmap.height, pixmap.width, pixmap.n)
    image = cv2.cvtColor(rgb[:, :, :3], cv2.COLOR_RGB2BGR)

    counts = [clean_region(image, region, args.dpi) for region in regions]

    ok, encoded = cv2.imencode(".png", image, [cv2.IMWRITE_PNG_COMPRESSION, 6])
    if not ok:
        raise RuntimeError("Could not encode the cleaned certificate page.")

    output = fitz.open()
    output_page = output.new_page(width=page_rect.width, height=page_rect.height)
    output_page.insert_image(output_page.rect, stream=encoded.tobytes())
    args.output_pdf.parent.mkdir(parents=True, exist_ok=True)
    output.save(args.output_pdf, deflate=True, garbage=4)
    output.close()
    source.close()

    print(json.dumps({"masked_pixels": counts, "dpi": args.dpi, "page": args.page}))


if __name__ == "__main__":
    main()
