<?php

namespace Database\Seeders;

use App\Models\Tour;
use Illuminate\Database\Seeder;

/**
 * Seeds the starter packages from config/tours.php into the database, where
 * the admin panel takes over. Runs only while the table is empty.
 */
class TourSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Bootstrap content only. Once the table has rows the admin panel owns
         * this data, and re-seeding would either clobber their edits or, if a
         * record has been renamed, silently recreate the original alongside it.
         */
        if (Tour::query()->exists()) {
            $this->command?->info('  tours already has data - skipping.');

            return;
        }
        $tours = config('tours', []);

        foreach (array_values($tours) as $index => $tour) {
            $slug = array_keys($tours)[$index];

            Tour::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $tour['name'],
                    'tagline' => $tour['tagline'] ?? null,
                    'category' => $tour['category'],
                    'region' => $tour['region'] ?? null,
                    'tier' => $tour['tier'] ?? null,
                    'difficulty' => $tour['difficulty'] ?? 'Easy',
                    'image' => $tour['image'] ?? null,
                    'gallery' => $tour['gallery'] ?? [],
                    'days' => $tour['days'],
                    'nights' => $tour['nights'] ?? null,
                    'group' => $tour['group'] ?? null,
                    'location' => $tour['location'] ?? null,
                    'best_time' => $tour['best_time'] ?? null,
                    'start' => $tour['start'] ?? null,
                    'end' => $tour['end'] ?? null,
                    'rating' => $tour['rating'] ?? 5.0,

                    // Config stored review counts as strings like "412".
                    'reviews' => (int) str_replace(',', '', (string) ($tour['reviews'] ?? 0)),

                    'price' => $tour['price'],
                    'price_note' => $tour['price_note'] ?? null,
                    'highlight' => $tour['highlight'] ?? null,
                    'summary' => $tour['summary'] ?? [],
                    'highlights' => $tour['highlights'] ?? [],
                    'itinerary' => $tour['itinerary'] ?? [],
                    'included' => $tour['included'] ?? [],
                    'excluded' => $tour['excluded'] ?? [],
                    'is_published' => true,
                    'is_featured' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
