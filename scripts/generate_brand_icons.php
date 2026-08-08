<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourcePath = $root.'/public/images/studentedge-icon.png';
$outputPath = $root.'/public/images/pwa';

if (! extension_loaded('gd') || ! is_file($sourcePath)) {
    fwrite(STDERR, "GD and public/images/studentedge-icon.png are required.\n");
    exit(1);
}

$source = imagecreatefrompng($sourcePath);
if (! $source) {
    fwrite(STDERR, "Unable to read {$sourcePath}.\n");
    exit(1);
}

function cropWhitePadding(\GdImage $source): \GdImage
{
    $width = imagesx($source);
    $height = imagesy($source);
    $left = $width;
    $top = $height;
    $right = 0;
    $bottom = 0;

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($source, $x, $y);
            $red = ($rgb >> 16) & 0xFF;
            $green = ($rgb >> 8) & 0xFF;
            $blue = $rgb & 0xFF;
            if (min($red, $green, $blue) >= 235) {
                continue;
            }

            $left = min($left, $x);
            $top = min($top, $y);
            $right = max($right, $x);
            $bottom = max($bottom, $y);
        }
    }

    if ($right <= $left || $bottom <= $top) {
        return $source;
    }

    return imagecrop($source, [
        'x' => max(0, $left - 8),
        'y' => max(0, $top - 8),
        'width' => min($width - max(0, $left - 8), $right - $left + 17),
        'height' => min($height - max(0, $top - 8), $bottom - $top + 17),
    ]);
}

function roundedRectangle(\GdImage $image, int $left, int $top, int $right, int $bottom, int $radius, int $color): void
{
    imagefilledrectangle($image, $left + $radius, $top, $right - $radius, $bottom, $color);
    imagefilledrectangle($image, $left, $top + $radius, $right, $bottom - $radius, $color);
    imagefilledellipse($image, $left + $radius, $top + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($image, $right - $radius, $top + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($image, $left + $radius, $bottom - $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($image, $right - $radius, $bottom - $radius, $radius * 2, $radius * 2, $color);
}

function createBrandIcon($source, int $size, float $logoScale, bool $rounded = false): \GdImage
{
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    $background = imagecolorallocatealpha($canvas, 255, 253, 249, 0);
    imagefill($canvas, 0, 0, $rounded ? $transparent : $background);
    if ($rounded) {
        roundedRectangle($canvas, 0, 0, $size - 1, $size - 1, max(3, (int) round($size * .24)), $background);
    }

    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);
    $targetSize = (int) round($size * $logoScale);
    $x = (int) round(($size - $targetSize) / 2);
    $y = (int) round(($size - $targetSize) / 2);

    imagealphablending($canvas, true);
    imagecopyresampled($canvas, $source, $x, $y, 0, 0, $targetSize, $targetSize, $sourceWidth, $sourceHeight);

    return $canvas;
}

function writePng(\GdImage $image, string $path): void
{
    imagepng($image, $path, 9);
    imagedestroy($image);
}

function transparentMark(\GdImage $source): \GdImage
{
    $width = imagesx($source);
    $height = imagesy($source);
    $mark = imagecreatetruecolor($width, $height);
    imagealphablending($mark, false);
    imagesavealpha($mark, true);
    imagefill($mark, 0, 0, imagecolorallocatealpha($mark, 0, 0, 0, 127));

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($source, $x, $y);
            $red = ($rgb >> 16) & 0xFF;
            $green = ($rgb >> 8) & 0xFF;
            $blue = $rgb & 0xFF;
            if (min($red, $green, $blue) >= 238) {
                continue;
            }

            imagesetpixel($mark, $x, $y, imagecolorallocatealpha($mark, $red, $green, $blue, 0));
        }
    }

    return $mark;
}

$croppedSource = cropWhitePadding($source);
writePng(transparentMark($croppedSource), $root.'/public/images/studentedge-mark.png');

foreach ([16, 32, 180, 192, 512] as $size) {
    $scale = $size <= 32 ? .88 : .68;
    writePng(createBrandIcon($croppedSource, $size, $scale, $size <= 32), $outputPath.'/icon-'.$size.'.png');
}

$favicon = file_get_contents($outputPath.'/icon-32.png');
$ico = pack('vvv', 0, 1, 1)
    . pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($favicon), 22)
    . $favicon;
file_put_contents($root.'/public/favicon.ico', $ico);

if ($croppedSource !== $source) {
    imagedestroy($croppedSource);
}
imagedestroy($source);
echo "StudentEdge browser and PWA icons generated.\n";
