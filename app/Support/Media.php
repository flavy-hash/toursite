<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resolves an image reference to something usable in a src attribute.
 *
 * Three shapes exist side by side across the site:
 *   - an external absolute URL, kept as-is;
 *   - a file committed under /public ("/assets/images/…"), used as-is;
 *   - an admin upload on the public disk, saved as a bare key
 *     ("tours/abc.jpg") and resolved through the storage symlink.
 *
 * The result is deliberately root-relative so it resolves against whatever
 * host is actually serving the request.
 */
class Media
{
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, '/')) {
            return $path;
        }

        $url = Storage::disk('public')->url($path);

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }
}
