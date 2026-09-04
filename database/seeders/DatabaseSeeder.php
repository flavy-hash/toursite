<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Bring a fresh install up to a usable state.
     *
     * Every seeder here is idempotent, so this is safe to re-run: existing
     * rows are updated rather than duplicated.
     */
    public function run(): void
    {
        $this->call([
            TourSeeder::class,
            NavItemSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
