<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

/**
 * Moves the placeholder testimonials out of config/site.php and into the
 * reviews table, where they can be edited or removed in the admin panel.
 *
 * These are sample entries, not real feedback — delete them once genuine
 * reviews arrive.
 */
class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_values(config('site.stories', [])) as $index => $story) {
            Review::updateOrCreate(
                ['name' => $story['name'], 'source' => 'sample'],
                [
                    'location' => $story['from'] ?? null,
                    'tour_name' => $story['trip'] ?? null,
                    'body' => $story['quote'],
                    'rating' => $story['rating'] ?? 5,
                    'travelled_on' => isset($story['when'])
                        ? \Illuminate\Support\Carbon::parse('1 ' . $story['when'])
                        : null,
                    'is_published' => true,
                    'is_featured' => $index < 3,
                ]
            );
        }
    }
}
