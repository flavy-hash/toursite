<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

/**
 * Seeds the placeholder testimonials from config/site.php into the reviews
 * table. Runs only while the table is empty.
 *
 * These are sample entries, not real feedback — delete them once genuine
 * reviews arrive.
 */
class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Bootstrap content only. Once the table has rows the admin panel owns
         * this data, and re-seeding would either clobber their edits or, if a
         * record has been renamed, silently recreate the original alongside it.
         */
        if (Review::query()->exists()) {
            $this->command?->info('  reviews already has data - skipping.');

            return;
        }
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
