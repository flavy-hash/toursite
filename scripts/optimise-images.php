<?php

/**
 * Downscales and re-encodes images in a directory, in place.
 *
 * Intended for the *staged* copy only. Admin uploads come straight off a
 * camera or phone at several megabytes, which no browser needs: capping the
 * long edge and re-encoding cuts both the deployment size and the page weight
 * every visitor downloads.
 *
 * Usage: php optimise-images.php <directory> [maxEdge] [quality]
 */

$dir = $argv[1] ?? null;
$maxEdge = (int) ($argv[2] ?? 1920);
$quality = (int) ($argv[3] ?? 82);

if (! $dir || ! is_dir($dir)) {
    fwrite(STDERR, "  usage: php optimise-images.php <directory> [maxEdge] [quality]\n");
    exit(1);
}

if (! extension_loaded('gd')) {
    fwrite(STDERR, "  GD extension not available; skipping image optimisation.\n");
    exit(0);
}

$before = 0;
$after = 0;
$touched = 0;
$skipped = 0;

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));

foreach ($files as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $path = $file->getPathname();
    $size = $file->getSize();
    $info = @getimagesize($path);

    if (! $info) {
        continue;
    }

    $before += $size;

    [$width, $height, $type] = $info;

    $source = match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG => @imagecreatefrompng($path),
        IMAGETYPE_WEBP => @imagecreatefromwebp($path),
        default => null,
    };

    if (! $source) {
        $after += $size;
        $skipped++;
        continue;
    }

    $scale = min(1, $maxEdge / max($width, $height));
    $newWidth = max(1, (int) round($width * $scale));
    $newHeight = max(1, (int) round($height * $scale));

    $canvas = imagecreatetruecolor($newWidth, $newHeight);

    // Keep transparency on PNG and WebP rather than filling it black.
    if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
    }

    imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Write beside the original first, so a failed encode cannot destroy the
    // only copy of a photo.
    $temp = $path . '.tmp';

    $ok = match ($type) {
        IMAGETYPE_JPEG => imagejpeg($canvas, $temp, $quality),
        IMAGETYPE_PNG => imagepng($canvas, $temp, 6),
        IMAGETYPE_WEBP => imagewebp($canvas, $temp, $quality),
        default => false,
    };

    imagedestroy($canvas);
    imagedestroy($source);

    if (! $ok || ! is_file($temp)) {
        @unlink($temp);
        $after += $size;
        $skipped++;
        continue;
    }

    // Only keep the new file if it is actually smaller; re-encoding an
    // already-optimised image can make it bigger.
    if (filesize($temp) < $size) {
        rename($temp, $path);
        $after += filesize($path);
        $touched++;
    } else {
        unlink($temp);
        $after += $size;
        $skipped++;
    }
}

printf(
    "  optimised %d images (%d left alone): %.1f MB -> %.1f MB, saving %.1f MB\n",
    $touched,
    $skipped,
    $before / 1048576,
    $after / 1048576,
    ($before - $after) / 1048576
);
