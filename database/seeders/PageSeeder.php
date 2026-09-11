<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Starter content for /about and /about/team.
 *
 * Written to be replaced: it describes the business in general terms so the
 * pages are not empty on a fresh install, and everything here is editable in
 * the panel under Site -> Pages.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $slug => $attributes) {
            /*
             * Per page rather than per table: adding a second page later must
             * not be blocked by the first already existing, and an existing
             * page must never be overwritten once staff have edited it.
             */
            if (Page::where('slug', $slug)->exists()) {
                $this->command?->info("  page [{$slug}] already exists - skipping.");

                continue;
            }

            Page::create(['slug' => $slug] + $attributes);
        }
    }

    /** @return array<string, array<string, mixed>> */
    private function pages(): array
    {
        $brand = config('site.brand.name');

        return [
            Page::ABOUT => [
                'title' => 'About Us',
                'eyebrow' => 'Who we are',
                'heading' => 'A Tanzanian operator, run by Tanzanians',
                'intro' => "{$brand} is based in Arusha, at the foot of Mount Meru and a "
                    . 'morning\'s drive from the Serengeti. We plan and run every trip '
                    . 'ourselves — no agents, no handing you over at the airport.',
                'hero_image' => config('site.page_headers.tours'),
                'sections' => [
                    [
                        'heading' => 'How we work',
                        'body' => 'Every itinerary is put together for the people travelling on it. '
                            . 'We ask what you want out of the trip, then build around it — the pace, '
                            . 'the camps, how long you sit with a sighting. Nothing here is sold off a shelf.',
                        'image' => null,
                    ],
                    [
                        'heading' => 'Our guides',
                        'body' => 'Our guides are licensed, grew up in these regions, and have been '
                            . 'reading this landscape their whole lives. They are the reason the same '
                            . 'park looks different with us.',
                        'image' => null,
                    ],
                    [
                        'heading' => 'Where your money goes',
                        'body' => 'We employ locally, buy locally and pay park fees that fund the '
                            . 'conservation these animals depend on. A trip with us keeps money in '
                            . 'the communities you travel through.',
                        'image' => null,
                    ],
                ],
                'meta_description' => "{$brand} is a Tanzanian-owned safari and trekking operator "
                    . 'based in Arusha, planning and running every trip ourselves.',
            ],

            Page::TEAM => [
                'title' => 'Our Team',
                'eyebrow' => 'The people behind the trips',
                'heading' => 'Meet the team',
                'intro' => 'The planners, guides and drivers who put your trip together and '
                    . 'see it through on the ground.',
                'hero_image' => config('site.page_headers.reviews'),
                'sections' => [],
                'meta_description' => "Meet the guides, planners and drivers behind {$brand}.",
            ],

            Page::FAQ => [
                'title' => 'Frequently Asked Questions',
                'eyebrow' => 'Good to know',
                'heading' => 'Questions travellers ask us',
                'intro' => 'Visas, vaccinations, when the migration crosses, what to pack, and how '
                    . 'payment works. If yours is not here, just ask.',
                'hero_image' => config('site.page_headers.inquiry'),
                'sections' => [],
                'meta_title' => 'Safari FAQ',
                'meta_description' => 'Answers to the questions travellers ask before a Tanzanian '
                    . 'safari or Kilimanjaro climb — visas, timing, packing, health and payment.',
            ],
        ];
    }
}
