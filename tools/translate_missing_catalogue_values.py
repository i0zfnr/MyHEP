"""Translate newly synchronized, still-untranslated EN/MS UI catalogue values.

Only static translation keys are sent to the translation endpoint. Laravel
placeholders and StudentEdge terminology are masked or normalized afterwards.
Existing reviewed translations are never overwritten.
"""

from __future__ import annotations

import json
import re
import time
import urllib.parse
import urllib.request
import os

from sync_translation_catalogues import REVIEWED_EN, REVIEWED_MS, looks_malay
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TOKEN = re.compile(r"(:[A-Za-z_][A-Za-z0-9_]*|\{\{.*?\}\}|https?://\S+|\b(?:JHEP|TPSA|TPSP|TPA|NRIC|IC|OKU|B40|CSV|DOCX|PDF|QR|GPS|AI|API|PHP|Laravel|StudentEdge)\b)")


def write_catalogue(path: Path, values: dict[str, str]) -> None:
    temporary = path.with_suffix(path.suffix + ".tmp")
    temporary.write_text(json.dumps(values, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")
    for attempt in range(5):
        try:
            os.replace(temporary, path)
            return
        except OSError:
            if attempt == 4:
                raise
            time.sleep(0.5 * (attempt + 1))


def mask(text: str) -> tuple[str, dict[str, str]]:
    values: dict[str, str] = {}

    def replace(match: re.Match[str]) -> str:
        marker = f"ZXQTK{len(values):03d}QXZ"
        values[marker] = match.group(0)
        return marker

    return TOKEN.sub(replace, text), values


def restore(text: str, values: dict[str, str]) -> str:
    for marker, value in values.items():
        text = re.sub(re.escape(marker), value, text, flags=re.IGNORECASE)
    return text


def translate(text: str, target: str, source: str = "auto") -> str:
    masked, values = mask(text)
    params = urllib.parse.urlencode({
        "client": "gtx", "sl": source, "tl": target, "dt": "t", "q": masked,
    })
    url = "https://translate.googleapis.com/translate_a/single?" + params
    last_error: Exception | None = None
    for attempt in range(4):
        try:
            with urllib.request.urlopen(url, timeout=20) as response:
                payload = json.loads(response.read().decode("utf-8"))
            result = "".join(part[0] for part in payload[0] if part and part[0])
            return restore(result, values)
        except Exception as error:  # pragma: no cover - network retry path
            last_error = error
            time.sleep(1.5 * (attempt + 1))
    raise RuntimeError(f"Translation failed for {text!r}: {last_error}")


def translate_batch(texts: list[str], target: str, source: str = "auto") -> list[str]:
    separator = "ZXQSEPARATORQXZ"
    translated = translate((f"\n{separator}\n").join(texts), target, source)
    parts = re.split(rf"\s*{separator}\s*", translated, flags=re.IGNORECASE)
    if len(parts) != len(texts):
        return [translate(text, target, source) for text in texts]
    return parts


def polish(value: str, target: str) -> str:
    replacements = {
        "ms": {
            "Nombor matrikulasi": "Nombor matrik",
            "nombor matrikulasi": "nombor matrik",
            "Pengarah Program": "Pengarah Program",
            "Hal Ehwal Pelajar": "Hal Ehwal Pelajar",
            "Biasiswa": "Biasiswa",
            "Pentadbir": "Admin",
            "Papan Pemuka": "Dashboard",
        },
        "en": {
            "matrix number": "matric number",
            "Matrix number": "Matric number",
            "Student Affairs Department": "Student Affairs Department",
        },
    }
    for old, new in replacements[target].items():
        value = value.replace(old, new)
    return value


def main() -> None:
    review_path = ROOT / ".tmp" / "translation-review-required.json"
    review = json.loads(review_path.read_text(encoding="utf-8")) if review_path.exists() else {"en": [], "ms": []}
    for locale, target in (("en", "en"), ("ms", "ms")):
        path = ROOT / "lang" / f"{locale}.json"
        current = json.loads(path.read_text(encoding="utf-8-sig"))
        pending = [key for key in review.get(locale, []) if current.get(key) == key]
        print(f"{locale}: translating {len(pending)} unresolved values")
        completed = 0
        batch: list[str] = []
        batch_length = 0
        for key in pending + [""]:
            if batch and (not key or batch_length + len(key) > 3200 or len(batch) >= 30):
                values = translate_batch(batch, target)
                for source, value in zip(batch, values):
                    current[source] = polish(value, target)
                completed += len(batch)
                print(f"  {completed}/{len(pending)}")
                write_catalogue(path, current)
                batch, batch_length = [], 0
            if key:
                batch.append(key)
                batch_length += len(key)
        write_catalogue(path, dict(sorted(current.items())))

        # Short English labels can be misdetected as Malay (for example,
        # "Import Staff"). Retry unchanged English-looking MS values with an
        # explicit source language.
        if target == "ms":
            retry = [key for key in pending if current.get(key) == key and not looks_malay(key)]
            for start in range(0, len(retry), 30):
                batch = retry[start : start + 30]
                values = translate_batch(batch, "ms", "en")
                for source_text, value in zip(batch, values):
                    current[source_text] = polish(value, "ms")
            write_catalogue(path, dict(sorted(current.items())))
            print(f"  retried {len(retry)} short English values with explicit source=en")

        current.update(REVIEWED_MS if target == "ms" else REVIEWED_EN)
        write_catalogue(path, dict(sorted(current.items())))


if __name__ == "__main__":
    main()
