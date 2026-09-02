<?php

namespace Database\Seeders;

use App\Models\Tour;
use Illuminate\Database\Seeder;

/**
 * Moves the four packages that used to live in config/tours.php into the
 * database, where the admin panel can edit them. Idempotent — re-running it
 * updates the existing rows rather than duplicating them.
 */
class TourSeeder extends Seeder
{
    public function run(): void
    {
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
