<?php

namespace Database\Seeders;

use App\Models\NavItem;
use Illuminate\Database\Seeder;

/**
 * Seeds the navigation from config/site.php into the database, where the admin
 * panel takes over. Runs only while the table is empty.
 */
class NavItemSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Bootstrap content only. Once the table has rows the admin panel owns
         * this data, and re-seeding would either clobber their edits or, if a
         * record has been renamed, silently recreate the original alongside it.
         */
        if (NavItem::query()->exists()) {
            $this->command?->info('  nav_items already has data - skipping.');

            return;
        }
        foreach (array_values(config('site.nav', [])) as $index => $item) {
            $panel = $item['panel'] ?? [];

            NavItem::updateOrCreate(
                ['location' => NavItem::HEADER, 'label' => $item['name']],
                [
                    'path' => $item['path'],
                    'sort_order' => $index,
                    'is_active' => true,
                    'panel_heading' => $panel['heading'] ?? null,
                    'panel_copy' => $panel['copy'] ?? null,
                    'panel_cta_label' => $panel['cta']['label'] ?? null,
                    'panel_cta_path' => $panel['cta']['path'] ?? null,
                    'panel_image' => $panel['image'] ?? null,
                    'rail' => $panel['rail'] ?? null,
                ]
            );
        }

        foreach (array_values(config('site.bottom_nav', [])) as $index => $item) {
            NavItem::updateOrCreate(
                ['location' => NavItem::BOTTOM, 'label' => $item['label']],
                [
                    'path' => $item['path'],
                    'icon' => $item['icon'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
