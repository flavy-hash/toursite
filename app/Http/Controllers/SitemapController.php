<?php

namespace App\Http\Controllers;

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
