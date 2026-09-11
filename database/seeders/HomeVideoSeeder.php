<?php

namespace Database\Seeders;

use App\Models\HomeVideo;
use Illuminate\Database\Seeder;

/**
 * The homepage video panel.
 *
 * Seeded unpublished and with no video: there is no TWINS AFRICAN clip to
 * point at, and putting somebody else's video on the homepage would be worse
 * than showing nothing. Set the ID under Site -> Homepage Video and switch it
 * on there.
 */
class HomeVideoSeeder extends Seeder
{
    public function run(): void
    {
        if (HomeVideo::query()->exists()) {
            $this->command?->info('  home_videos already has data - skipping.');

            return;
        }

        HomeVideo::create([
            'youtube_id' => '',
            'eyebrow' => 'From our channel',
            'heading' => 'Take a glimpse into the safari',
            'video_title' => null,
            'caption' => 'Filmed by our guides',
            'is_published' => false,
        ]);
    }
}
