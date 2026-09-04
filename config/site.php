<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

    'brand' => [
        'name' => 'TWINS AFRICAN',
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
                'href' => '/tours?region=northern',
            ],
            [
                'image' => '/assets/images/kili.jpg',
                'title' => 'Mount Kilimanjaro',
                'subtitle' => "Conquer Africa's Highest Peak",
                'location' => 'Kilimanjaro Region',
                'rating' => '4.8',
                'price' => 'From $1,850',
                'href' => '/tours?region=kilimanjaro',
            ],
            [
                'image' => '/assets/images/carousel/Rhinos_in_Ngorongoro_Crater.jpg',
                'title' => 'Ngorongoro Crater',
                'subtitle' => "The World's Largest Caldera",
                'location' => 'Arusha Region',
                'rating' => '4.9',
                'price' => 'From $1,200',
                'href' => '/tours?region=northern',
            ],
            [
                'image' => '/assets/images/carousel/zanzibar_beach.jpg',
                'title' => 'Zanzibar Beaches',
                'subtitle' => 'Paradise Island Retreat',
                'location' => 'Zanzibar Archipelago',
                'rating' => '4.7',
                'price' => 'From $890',
                'href' => '/tours?region=zanzibar',
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
            'name' => 'Safaris',
            'path' => '/tours?category=wildlife',
            'panel' => [
                'heading' => 'Tanzania Safari Adventures',
                'copy' => 'Serengeti, Ngorongoro, Tarangire and Lake Manyara with Tanzanian guides who build each route around where the animals actually are this week.',
                'cta' => ['label' => 'Explore Safaris', 'path' => '/tours?category=wildlife'],
                'image' => '/assets/images/carousel/lionss_with_her_cub.jpg',
                'rail' => [
                    ['name' => 'View All Safaris', 'path' => '/tours?category=wildlife'],
                    ['name' => 'Budget', 'path' => '/tours?category=wildlife&tier=budget'],
                    ['name' => 'Classic', 'path' => '/tours?category=wildlife&tier=classic'],
                    ['name' => 'Mid-range', 'path' => '/tours?category=wildlife&tier=mid-range'],
                    ['name' => 'Luxury', 'path' => '/tours?category=wildlife&tier=luxury'],
                ],
            ],
        ],
        [
            'name' => 'Southern Circuit',
            'path' => '/tours?region=southern',
            'panel' => [
                'heading' => 'Tanzania Southern Circuit',
                'copy' => 'The uncrowded south — Ruaha, Nyerere (Selous) and Mikumi. Big herds, big cats and boat safaris, well away from the busier northern parks.',
                'cta' => ['label' => 'Explore the South', 'path' => '/tours?region=southern'],
                'image' => '/assets/images/carousel/Rhinos_in_Ngorongoro_Crater.jpg',
                'rail' => [
                    ['name' => 'View All Southern', 'path' => '/tours?region=southern'],
                    ['name' => 'Ruaha National Park', 'path' => '/tours?region=southern&park=ruaha'],
                    ['name' => 'Nyerere · Selous', 'path' => '/tours?region=southern&park=nyerere'],
                    ['name' => 'Mikumi National Park', 'path' => '/tours?region=southern&park=mikumi'],
                    ['name' => 'Udzungwa Mountains', 'path' => '/tours?region=southern&park=udzungwa'],
                ],
            ],
        ],
        [
            'name' => 'Kilimanjaro',
            'path' => '/tours?region=kilimanjaro',
            'panel' => [
                'heading' => 'Climbing the Roof of Africa',
                'copy' => 'Rainforest to glacier at 5,895 m — guided climbs with proper gear, honest acclimatisation schedules and crews who are paid properly.',
                'cta' => ['label' => 'Climb Kilimanjaro', 'path' => '/tours?region=kilimanjaro'],
                'image' => '/assets/images/kili.jpg',
                'rail' => [
                    ['name' => 'Overview', 'path' => '/tours?region=kilimanjaro'],
                    ['name' => 'Machame · 7 Days', 'path' => '/tours/kilimanjaro-machame'],
                    ['name' => 'Lemosho · 8 Days', 'path' => '/tours/kilimanjaro-lemosho'],
                    ['name' => 'Marangu · 6 Days', 'path' => '/tours/kilimanjaro-marangu'],
                    ['name' => 'Day Hikes on the Slopes', 'path' => '/tours?category=mountain&duration=1'],
                ],
            ],
        ],
        [
            'name' => 'Zanzibar',
            'path' => '/tours?region=zanzibar',
            'panel' => [
                'heading' => 'Zanzibar Island Paradise',
                'copy' => 'Finish the safari barefoot — dhow cruises, Stone Town alleyways, spice farms and the turquoise water off Mnemba Atoll.',
                'cta' => ['label' => 'Explore Zanzibar', 'path' => '/tours?region=zanzibar'],
                'image' => '/assets/images/carousel/zanzibar_beach.jpg',
                'rail' => [
                    ['name' => 'Beach Holidays', 'path' => '/tours?region=zanzibar'],
                    ['name' => 'Stone Town Tours', 'path' => '/tours?category=beach&activity=stone-town'],
                    ['name' => 'Spice Tours', 'path' => '/tours?category=beach&activity=spice'],
                    ['name' => 'Boat Trips & Snorkeling', 'path' => '/tours?category=beach&activity=snorkeling'],
                ],
            ],
        ],
        [
            'name' => 'About',
            'path' => '/about',
            'panel' => [
                'heading' => 'We are TWINS AFRICAN',
                'copy' => 'A Tanzanian-owned operator based in Arusha — the guides, planners and drivers who put every itinerary together.',
                'cta' => ['label' => 'Meet Our Team', 'path' => '/about/team'],
                'image' => '/assets/images/carousel/lionss_with_her_cub.jpg',
                'rail' => [
                    ['name' => 'About Us', 'path' => '/about'],
                    ['name' => 'Our Team', 'path' => '/about/team'],
                    ['name' => 'Reviews', 'path' => '/reviews'],
                    ['name' => 'FAQ', 'path' => '/planning?section=faqs'],
                ],
            ],
        ],
        [
            'name' => 'Contact',
            // Anchors the footer, which carries the contact details and is
            // present on every page.
            'path' => '#contact',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Page header artwork
    |--------------------------------------------------------------------------
    |
    | Background photograph behind the title band on interior pages.
    |
    */

    'page_headers' => [
        'tours' => '/assets/images/carousel/lionss_with_her_cub.jpg',
        'inquiry' => '/assets/images/carousel/zanzibar_beach.jpg',
        'reviews' => '/assets/images/carousel/Rhinos_in_Ngorongoro_Crater.jpg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Featured destinations
    |--------------------------------------------------------------------------
    */

    'destinations' => [
        [
            'name' => 'Serengeti National Park',
            'region' => 'Northern Tanzania',
            'tagline' => 'Follow the Great Migration across 14,750 km² of open plain.',
            'image' => '/assets/images/carousel/lionss_with_her_cub.jpg',
            'best' => 'Jun – Oct',
            'price' => 'From $2,450',
            'href' => '/tours?region=northern',
            'featured' => true,
        ],
        [
            'name' => 'Ngorongoro Crater',
            'region' => 'Arusha Region',
            'tagline' => "The world's largest intact caldera, and the densest wildlife in Africa.",
            'image' => '/assets/images/carousel/Rhinos_in_Ngorongoro_Crater.jpg',
            'best' => 'Year round',
            'price' => 'From $1,200',
            'href' => '/tours?region=northern',
        ],
        [
            'name' => 'Mount Kilimanjaro',
            'region' => 'Kilimanjaro Region',
            'tagline' => 'Five climate zones between the gate and Uhuru Peak at 5,895 m.',
            'image' => '/assets/images/kili.jpg',
            'best' => 'Jan – Mar',
            'price' => 'From $1,850',
            'href' => '/tours?region=kilimanjaro',
        ],
        [
            'name' => 'Zanzibar Beaches',
            'region' => 'Zanzibar Archipelago',
            'tagline' => 'Stone Town spice markets, then white sand and a dhow at sunset.',
            'image' => '/assets/images/carousel/zanzibar_beach.jpg',
            'best' => 'Jun – Oct',
            'price' => 'From $890',
            'href' => '/tours?region=zanzibar',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | What we stand for
    |--------------------------------------------------------------------------
    */

    'pillars' => [
        [
            'icon' => 'compass',
            'title' => 'Adventure First',
            'copy' => 'Itineraries built around where the wildlife actually is that week, not a fixed route sold to everyone.',
        ],
        [
            'icon' => 'info',
            'title' => 'Expert Guidance',
            'copy' => 'Tanzanian guides who grew up on these routes, with local knowledge and international standards of service.',
        ],
        [
            'icon' => 'pin',
            'title' => 'Sustainable Tourism',
            'copy' => 'Community-owned camps, fair wages for crew, and a share of every booking going back into conservation.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Traveller stories
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER COPY — sample reviews for layout only. Replace every entry
    | with real, attributable feedback before this page goes live.
    |
    */

    'stories' => [
        [
            'quote' => 'Our guide read the plains like a map. We were parked at the river crossing twenty minutes before the herd arrived — everyone else turned up late.',
            'name' => 'Amara Okafor',
            'from' => 'Nairobi, Kenya',
            'trip' => 'Great Migration Safari',
            'when' => 'February 2025',
            'rating' => 5,
        ],
        [
            'quote' => 'Eight days on Machame and the crew never once let it feel like an endurance test. Summit morning was the hardest and best thing we have done.',
            'name' => 'David & Sarah Chen',
            'from' => 'Singapore',
            'trip' => 'Kilimanjaro · Machame Route',
            'when' => 'January 2025',
            'rating' => 5,
        ],
        [
            'quote' => 'The crater floor at first light, with mist still sitting in it, is the single most extraordinary place I have ever stood.',
            'name' => 'Emma Rodriguez',
            'from' => 'Barcelona, Spain',
            'trip' => 'Ngorongoro Crater Descent',
            'when' => 'December 2024',
            'rating' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'email' => 'hello@twinsafrican.co.tz',
        'phone' => '+255 754 332 741',
        'address' => 'Arusha, Tanzania',

        // Digits only, international format, no "+" — this is what wa.me expects.
        'whatsapp' => '255754332741',
        'whatsapp_message' => 'Hi TWINS AFRICAN! I am interested in booking a safari.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Social profiles
    |--------------------------------------------------------------------------
    |
    | Shown on the site and listed as "sameAs" in the structured data, which is
    | how search engines tie these accounts to the business. Remove a line to
    | drop that icon; add 'youtube' or 'x' when those accounts exist.
    |
    */

    'social' => [
        'instagram' => 'https://www.instagram.com/twinsafricantravel/',
        'facebook' => 'https://www.facebook.com/TwinsAfricanTravel',
        'tiktok' => 'https://tiktok.com/@twinsafricantravel',
    ],

    /*
    |--------------------------------------------------------------------------
    | Awards and badges
    |--------------------------------------------------------------------------
    |
    | Shown as circular badges under the reviews on the homepage. The image is
    | the official asset from the awarding body — replace it with the exact
    | file they issue you each year rather than an edited copy.
    |
    */

    'awards' => [
        [
            'name' => "Tripadvisor Travellers' Choice",
            'image' => '/assets/images/OIP_5.webp',
            'url' => 'https://www.tripadvisor.com/Profile/TwinsAfricanTravel',
        ],
    ],

    'footer' => [
        [
            'heading' => 'Destinations',
            'items' => [
                ['name' => 'Serengeti', 'path' => '/tours?region=northern'],
                ['name' => 'Ngorongoro', 'path' => '/tours?region=northern'],
                ['name' => 'Kilimanjaro', 'path' => '/tours?region=kilimanjaro'],
                ['name' => 'Zanzibar', 'path' => '/tours?region=zanzibar'],
            ],
        ],
        [
            'heading' => 'Experiences',
            'items' => [
                ['name' => 'Wildlife Safaris', 'path' => '/tours?category=wildlife'],
                ['name' => 'Mountain Treks', 'path' => '/tours?category=mountain'],
                ['name' => 'Beach Escapes', 'path' => '/tours?category=beach'],
                ['name' => 'Cultural Tours', 'path' => '/tours?category=cultural'],
            ],
        ],
        [
            'heading' => 'Company',
            'items' => [
                ['name' => 'About Us', 'path' => '/about'],
                ['name' => 'Plan Your Trip', 'path' => '/planning'],
                ['name' => 'Contact', 'path' => '#contact'],
                ['name' => 'Privacy Policy', 'path' => '/privacy'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile bottom navigation
    |--------------------------------------------------------------------------
    |
    | Shown below the sm breakpoint in place of the drawer. Six items is the
    | practical ceiling before labels start wrapping on a narrow phone; each
    | "icon" names a path in the icon partial.
    |
    */

    'bottom_nav' => [
        ['label' => 'Home', 'path' => '/', 'icon' => 'home'],
        ['label' => 'Safaris', 'path' => '/tours?category=wildlife', 'icon' => 'compass'],
        ['label' => 'Southern', 'path' => '/tours?region=southern', 'icon' => 'pin'],
        ['label' => 'Climbing', 'path' => '/tours?region=kilimanjaro', 'icon' => 'mountain'],
        ['label' => 'Zanzibar', 'path' => '/tours?region=zanzibar', 'icon' => 'wave'],
        ['label' => 'Contact', 'path' => '#contact', 'icon' => 'mail'],
    ],

];
