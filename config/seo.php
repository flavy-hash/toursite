<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Used whenever a page does not set its own. Titles are suffixed with the
    | brand unless the page opts out.
    |
    */

    'title_suffix' => ' — TWINS AFRICAN Travel',

    'default_title' => 'Tanzania Safaris, Kilimanjaro Treks & Zanzibar Holidays',

    'default_description' => 'Tanzanian-owned tour operator in Arusha running privately guided '
        . 'safaris in the Serengeti and Ngorongoro, Kilimanjaro climbs and Zanzibar beach escapes.',

    // 1200x630 — the size Facebook, LinkedIn and X all crop from cleanly.
    'default_image' => '/assets/social/og-default.jpg',

    'locale' => 'en_GB',

    /*
    |--------------------------------------------------------------------------
    | Organisation
    |--------------------------------------------------------------------------
    |
    | Feeds the JSON-LD block search engines read for the knowledge panel.
    |
    */

    'organisation' => [
        'type' => 'TravelAgency',
        'name' => 'TWINS AFRICAN Travel',
        'locality' => 'Arusha',
        'region' => 'Arusha',
        'country' => 'TZ',
        'area_served' => 'Tanzania',
        'knows_about' => [
            'Serengeti National Park',
            'Ngorongoro Crater',
            'Mount Kilimanjaro',
            'Zanzibar',
            'The Great Migration',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social profiles
    |--------------------------------------------------------------------------
    |
    | Listed as sameAs in the structured data. Blank entries are skipped, so
    | leaving a profile out is safe.
    |
    */

    'social' => [
        // 'facebook' => 'https://facebook.com/…',
        // 'instagram' => 'https://instagram.com/…',
    ],

    // Without an account, X falls back to a large image card, which is fine.
    'twitter_handle' => null,

];
