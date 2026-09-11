<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Tour;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * XML sitemap of everything worth indexing.
     *
     * Built from the database rather than a static file, so a package
     * published in the admin panel is discoverable immediately.
     */
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0', 'freq' => 'weekly'],
            ['loc' => route('tours.index'), 'priority' => '0.9', 'freq' => 'weekly'],
        ];

        // Editable pages, listed only while published — the same rule the
        // routes apply, so the sitemap never points at a 404.
        foreach (Page::published()->get() as $page) {
            if (! $page->url()) {
                continue;
            }

            $urls[] = [
                'loc' => $page->url(),
                'lastmod' => $page->updated_at?->toAtomString(),
                'priority' => '0.6',
                'freq' => 'monthly',
            ];
        }

        foreach (Tour::published()->ordered()->get() as $tour) {
            $urls[] = [
                'loc' => route('tours.show', $tour->slug),
                'lastmod' => $tour->updated_at?->toAtomString(),
                'priority' => '0.8',
                'freq' => 'monthly',
                'image' => $tour->image_url ? url($tour->image_url) : null,
                'caption' => $tour->name,
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
