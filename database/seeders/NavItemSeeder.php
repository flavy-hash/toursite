<?php

namespace Database\Seeders;

use App\Models\NavItem;
use Illuminate\Database\Seeder;

/**
 * Moves the navigation out of config/site.php and into the database, where the
 * admin panel can edit it. Idempotent — matched on location plus label, so
 * re-running refreshes rather than duplicates.
 */
class NavItemSeeder extends Seeder
{
    public function run(): void
    {
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
