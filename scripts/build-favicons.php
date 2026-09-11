<?php

/**
 * Generates the browser-tab icons from the logo mark.
 *
 * Uses the full lockup exactly as supplied — mark, TWINS and "African Travel"
 * — on the brand's dark brown. It ships white on transparency, so nothing is
 * recoloured here; the dark ground is what keeps it visible against a light
 * tab strip.
 *
 * Worth knowing: the lockup is 5.5:1, so in a 16 or 32px tab it reduces to a
 * few pixels tall and the wording is not readable at that size. It reads
 * properly as a bookmark, an Apple touch icon and a PWA install prompt.
 *
 * Usage: php scripts/build-favicons.php
 */

$root = dirname(__DIR__);
$source = $root . '/public/assets/images/logo-side.png';
$public = $root . '/public';

// Brand dark brown, matching --color-dark-brown.
[$bgR, $bgG, $bgB] = [0x3a, 0x24, 0x18];

if (! is_file($source)) {
    fwrite(STDERR, "  source not found: {$source}\n");
    exit(1);
}

if (! extension_loaded('gd')) {
    fwrite(STDERR, "  GD extension not available.\n");
    exit(1);
}


/**
 * Crops the transparent margin off the artwork.
 *
 * The supplied file carries roughly 10% empty space each side, which would
 * otherwise be scaled down along with the mark and leave it marooned in the
 * middle of the tile.
 *
 * @return array{0: \GdImage, 1: int, 2: int}
 */
function trimTransparent(\GdImage $image): array
{
    $w = imagesx($image);
    $h = imagesy($image);

    $x0 = $w; $y0 = $h; $x1 = -1; $y1 = -1;

    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) < 100) {
                $x0 = min($x0, $x); $x1 = max($x1, $x);
                $y0 = min($y0, $y); $y1 = max($y1, $y);
            }
        }
    }

    if ($x1 < 0) {
        return [$image, $w, $h];
    }

    $cw = $x1 - $x0 + 1;
    $ch = $y1 - $y0 + 1;

    $cropped = imagecreatetruecolor($cw, $ch);
    imagealphablending($cropped, false);
    imagesavealpha($cropped, true);
    imagefilledrectangle($cropped, 0, 0, $cw, $ch, imagecolorallocatealpha($cropped, 0, 0, 0, 127));
    imagecopy($cropped, $image, 0, 0, $x0, $y0, $cw, $ch);
    imagedestroy($image);

    return [$cropped, $cw, $ch];
}

/**
 * One square icon: brown ground, white mark centred with breathing room.
 */
function icon(string $source, int $size, array $bg): \GdImage
{
    $mark = imagecreatefrompng($source);
    [$mark, $mw, $mh] = trimTransparent($mark);

    $canvas = imagecreatetruecolor($size, $size);
    imagefill($canvas, 0, 0, imagecolorallocate($canvas, ...$bg));

    // The lockup is 5.5:1, so width is always the limiting dimension.
    $box = (int) round($size * 0.90);
    $scale = min($box / $mw, $box / $mh);
    $w = max(1, (int) round($mw * $scale));
    $h = max(1, (int) round($mh * $scale));

    imagealphablending($canvas, true);

    $left = (int) round(($size - $w) / 2);
    $top = (int) round(($size - $h) / 2);

    imagecopyresampled(
        $canvas, $mark,
        $left, $top,
        0, 0,
        $w, $h, $mw, $mh
    );

    imagedestroy($mark);

    return $canvas;
}

$sizes = [
    'favicon-16x16.png' => 16,
    'favicon-32x32.png' => 32,
    'apple-touch-icon.png' => 180,
    'favicon-512x512.png' => 512,
];

foreach ($sizes as $name => $size) {
    $image = icon($source, $size, [$bgR, $bgG, $bgB]);
    imagepng($image, "{$public}/{$name}", 9);
    imagedestroy($image);
    printf("  %-24s %dx%d  %s\n", $name, $size, $size, filesizeHuman("{$public}/{$name}"));
}

/*
 * favicon.ico, holding a single 32x32 PNG.
 *
 * PNG-inside-ICO is understood by every browser still in use, and avoids
 * hand-rolling a BMP with its upside-down rows and AND mask.
 */
$png = file_get_contents("{$public}/favicon-32x32.png");

$ico = pack('vvv', 0, 1, 1)                      // reserved, type 1 (icon), 1 image
    . pack('CCCCvvVV',
        32, 32,                                   // width, height
        0,                                        // palette colours (0 = none)
        0,                                        // reserved
        1,                                        // colour planes
        32,                                       // bits per pixel
        strlen($png),                             // image data size
        22                                        // offset: 6 byte header + 16 byte entry
    )
    . $png;

file_put_contents("{$public}/favicon.ico", $ico);
printf("  %-24s %s\n", 'favicon.ico', filesizeHuman("{$public}/favicon.ico"));

function filesizeHuman(string $path): string
{
    $bytes = filesize($path);

    return $bytes > 1024 ? round($bytes / 1024) . ' KB' : $bytes . ' B';
}
