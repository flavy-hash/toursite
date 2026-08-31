<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

    'brand' => [
        'name' => 'MICO TOUR',
        'suffix' => 'Travel',
        'tagline' => 'Extraordinary Adventures in Tanzania',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hero carousel
    |--------------------------------------------------------------------------
    |
    | Slides shown in the homepage hero. The third slide leads because it is
    | the current featured adventure.
    |
    */

    'hero' => [
        'reviews' => '2,847',

        'slides' => [
            [
                'image' => '/assets/images/carousel/lionss_with_her_cub.jpg',
                'title' => 'Serengeti National Park',
                'subtitle' => 'Experience the Great Migration',
                'location' => 'Northern Tanzania',
                'rating' => '4.9',
                'price' => 'From $2,450',
                'href' => '/destinations/serengeti',
            ],
            [
                'image' => '/assets/images/Kilimanjaro_Trek.webp',
                'title' => 'Mount Kilimanjaro',
                'subtitle' => "Conquer Africa's Highest Peak",
                'location' => 'Kilimanjaro Region',
                'rating' => '4.8',
                'price' => 'From $1,850',
                'href' => '/destinations/kilimanjaro',
            ],
            [
                'image' => '/assets/images/carousel/Rhinos_in_Ngorongoro_Crater.jpg',
                'title' => 'Ngorongoro Crater',
                'subtitle' => "The World's Largest Caldera",
                'location' => 'Arusha Region',
                'rating' => '4.9',
                'price' => 'From $1,200',
                'href' => '/destinations/ngorongoro',
            ],
            [
                'image' => '/assets/images/carousel/zanzibar_beach.jpg',
                'title' => 'Zanzibar Beaches',
                'subtitle' => 'Paradise Island Retreat',
                'location' => 'Zanzibar Archipelago',
                'rating' => '4.7',
                'price' => 'From $890',
                'href' => '/destinations/zanzibar',
            ],
        ],

        'stats' => [
            ['value' => '100+', 'label' => 'Adventures'],
            ['value' => '100+', 'label' => 'Happy Travelers'],
            ['value' => '2+', 'label' => 'Years Experience'],
            ['value' => '4.9', 'label' => 'Average Rating'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Primary navigation
    |--------------------------------------------------------------------------
    |
    | Items without "columns" render as plain links; the rest open a mega menu.
    |
    */

    'nav' => [
        [
            'name' => 'Home',
            'path' => '/',
        ],
        [
            'name' => 'Destinations',
            'path' => '/destinations',
            'blurb' => 'Iconic parks, peaks and islands across Tanzania',
            'columns' => [
                [
                    'heading' => 'National Parks',
                    'items' => [
                        ['name' => 'Serengeti National Park', 'path' => '/destinations/serengeti'],
                        ['name' => 'Ngorongoro Crater', 'path' => '/destinations/ngorongoro'],
                        ['name' => 'Tarangire National Park', 'path' => '/destinations/tarangire'],
                        ['name' => 'Lake Manyara', 'path' => '/destinations/lake-manyara'],
                    ],
                ],
                [
                    'heading' => 'Peaks & Islands',
                    'items' => [
                        ['name' => 'Mount Kilimanjaro', 'path' => '/destinations/kilimanjaro'],
                        ['name' => 'Zanzibar Beaches', 'path' => '/destinations/zanzibar'],
                        ['name' => 'All Destinations', 'path' => '/destinations'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Tours & Experiences',
            'path' => '/tours',
            'blurb' => 'Safaris, treks, beach escapes and cultural journeys',
            'columns' => [
                [
                    'heading' => 'Wildlife Safaris',
                    'items' => [
                        ['name' => 'Serengeti Safaris', 'path' => '/tours?destination=Serengeti'],
                        ['name' => 'Ngorongoro Crater', 'path' => '/tours?destination=Ngorongoro'],
                        ['name' => 'Tarangire Wildlife', 'path' => '/tours?destination=Tarangire'],
                        ['name' => 'All Wildlife Tours', 'path' => '/tours?category=wildlife'],
                    ],
                ],
                [
                    'heading' => 'Mountain Adventures',
                    'items' => [
                        ['name' => 'Kilimanjaro Climbing', 'path' => '/tours?destination=Kilimanjaro'],
                        ['name' => 'Mount Meru Treks', 'path' => '/tours?destination=Meru'],
                        ['name' => 'Hiking Adventures', 'path' => '/tours?difficulty=challenging'],
                        ['name' => 'All Mountain Tours', 'path' => '/tours?category=mountain'],
                    ],
                ],
                [
                    'heading' => 'Beach Escapes',
                    'items' => [
                        ['name' => 'Zanzibar Beaches', 'path' => '/tours?destination=Zanzibar'],
                        ['name' => 'Island Hopping', 'path' => '/tours?category=beach&duration=4-7'],
                        ['name' => 'Luxury Beach Resorts', 'path' => '/tours?category=beach&accommodation=luxury'],
                        ['name' => 'All Beach Tours', 'path' => '/tours?category=beach'],
                    ],
                ],
                [
                    'heading' => 'Cultural Experiences',
                    'items' => [
                        ['name' => 'Maasai Village Visits', 'path' => '/tours?category=cultural&highlights=Maasai'],
                        ['name' => 'Local Cuisine Tours', 'path' => '/tours?category=cultural&activities=Cooking'],
                        ['name' => 'Traditional Crafts', 'path' => '/tours?category=cultural&duration=1-3'],
                        ['name' => 'All Cultural Tours', 'path' => '/tours?category=cultural'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Plan Your Trip',
            'path' => '/planning',
            'blurb' => 'Practical information for your Tanzania adventure',
            'columns' => [
                [
                    'heading' => 'Best Time to Visit',
                    'items' => [
                        ['name' => 'Dry Season (Jun-Oct)', 'path' => '/planning?section=when-to-visit'],
                        ['name' => 'Short Rains (Nov-Dec)', 'path' => '/planning?section=when-to-visit'],
                        ['name' => 'Long Rains (Mar-May)', 'path' => '/planning?section=when-to-visit'],
                        ['name' => 'Hot & Dry (Jan-Feb)', 'path' => '/planning?section=when-to-visit'],
                    ],
                ],
                [
                    'heading' => 'Trip Duration',
                    'items' => [
                        ['name' => '3-5 Day Itineraries', 'path' => '/planning?section=trip-duration'],
                        ['name' => '7-10 Day Itineraries', 'path' => '/planning?section=trip-duration'],
                        ['name' => '14+ Day Itineraries', 'path' => '/planning?section=trip-duration'],
                        ['name' => 'Combined Destinations', 'path' => '/planning?section=trip-duration'],
                    ],
                ],
                [
                    'heading' => 'Travel Essentials',
                    'items' => [
                        ['name' => 'Visa Requirements', 'path' => '/planning?section=travel-essentials'],
                        ['name' => 'Packing Lists', 'path' => '/planning?section=travel-essentials'],
                        ['name' => 'Health & Safety', 'path' => '/planning?section=travel-essentials'],
                        ['name' => 'Travel Insurance', 'path' => '/planning?section=travel-essentials'],
                    ],
                ],
                [
                    'heading' => 'FAQs',
                    'items' => [
                        ['name' => 'Safari FAQs', 'path' => '/planning?section=faqs'],
                        ['name' => 'Kilimanjaro FAQs', 'path' => '/planning?section=faqs'],
                        ['name' => 'Zanzibar FAQs', 'path' => '/planning?section=faqs'],
                        ['name' => 'General Travel FAQs', 'path' => '/planning?section=faqs'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'About Us',
            'path' => '/about',
        ],
        [
            'name' => 'Contact',
            'path' => '/contact',
        ],
    ],

];
