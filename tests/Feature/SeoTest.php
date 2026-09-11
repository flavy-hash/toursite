<?php

namespace Tests\Feature;

use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function tour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'slug' => 'seo-safari',
            'name' => 'SEO Safari',
            'tagline' => 'Seven days across the plains',
            'category' => 'Wildlife',
            'difficulty' => 'Easy',
            'image' => 'tours/hero.jpg',
            'days' => '7 Days',
            'price' => '$2,450',
            'rating' => 4.9,
            'reviews' => 412,
            'summary' => ['A paragraph about the trip.'],
            'itinerary' => [['day' => 1, 'title' => 'Arrive', 'copy' => 'Land in Arusha.']],
        ], $overrides));
    }

    /** @return array<int, array<string, mixed>> Every JSON-LD block on the page. */
    private function schema(string $html): array
    {
        preg_match_all(
            '#<script type="application/ld\+json">(.*?)</script>#s',
            $html,
            $matches
        );

        return collect($matches[1])
            ->map(function (string $json) {
                $decoded = json_decode(trim($json), true);

                $this->assertNotNull(
                    $decoded,
                    'JSON-LD must parse: ' . json_last_error_msg()
                );

                return $decoded;
            })
            /*
             * The site-wide blocks live inside one @graph, so flatten those
             * nodes up to the top level. Callers then look up a type the same
             * way whether it was emitted standalone or as part of the graph.
             */
            ->flatMap(fn (array $block) => isset($block['@graph'])
                ? $block['@graph']
                : [$block])
            ->all();
    }

    public function test_the_homepage_has_a_title_description_and_canonical(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Tanzania Safaris, Kilimanjaro Treks &amp; Zanzibar Holidays — TWINS AFRICAN Travel</title>', $html);
        $this->assertStringContainsString('<meta name="description"', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/') . '"', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $html);
    }

    public function test_social_tags_use_absolute_image_urls(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Scrapers do not resolve relative paths.
        preg_match('#<meta property="og:image" content="([^"]+)"#', $html, $m);

        $this->assertNotEmpty($m, 'og:image must be present');
        $this->assertStringStartsWith('http', $m[1]);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
    }

    public function test_every_page_carries_valid_organisation_schema(): void
    {
        $this->tour();

        foreach (['/', '/tours', '/tours/seo-safari'] as $path) {
            $blocks = $this->schema($this->get($path)->assertOk()->getContent());

            $types = array_column($blocks, '@type');
            $this->assertContains('TravelAgency', $types, "Missing organisation schema on {$path}");
        }
    }

    public function test_a_tour_page_describes_itself_as_a_trip_with_an_offer(): void
    {
        $this->tour();

        $blocks = $this->schema($this->get('/tours/seo-safari')->assertOk()->getContent());
        $trip = collect($blocks)->firstWhere('@type', 'TouristTrip');

        $this->assertNotNull($trip, 'Tour pages need TouristTrip schema');
        $this->assertSame('SEO Safari', $trip['name']);

        // "$2,450" has to become a number for the offer to be usable.
        // json_encode drops the trailing .0, so compare loosely on value.
        $this->assertEquals(2450, $trip['offers']['price']);
        $this->assertSame('USD', $trip['offers']['priceCurrency']);
        $this->assertSame(4.9, $trip['aggregateRating']['ratingValue']);
        $this->assertSame(412, $trip['aggregateRating']['reviewCount']);
    }

    public function test_a_tour_with_no_reviews_omits_the_rating(): void
    {
        // Claiming a rating with no reviews behind it is a structured-data
        // violation and gets flagged in Search Console.
        $this->tour(['slug' => 'unrated', 'name' => 'Unrated', 'reviews' => 0]);

        $blocks = $this->schema($this->get('/tours/unrated')->assertOk()->getContent());
        $trip = collect($blocks)->firstWhere('@type', 'TouristTrip');

        $this->assertNull($trip['aggregateRating']);
    }

    public function test_a_tour_page_has_breadcrumbs(): void
    {
        $this->tour();

        $blocks = $this->schema($this->get('/tours/seo-safari')->assertOk()->getContent());
        $crumbs = collect($blocks)->firstWhere('@type', 'BreadcrumbList');

        $this->assertNotNull($crumbs);
        $this->assertSame(['Home', 'Tours', 'SEO Safari'], array_column($crumbs['itemListElement'], 'name'));
    }

    public function test_a_tour_page_shares_its_own_photo(): void
    {
        $this->tour();

        preg_match(
            '#<meta property="og:image" content="([^"]+)"#',
            $this->get('/tours/seo-safari')->assertOk()->getContent(),
            $m
        );

        $this->assertStringContainsString('/storage/tours/hero.jpg', $m[1]);
    }

    public function test_the_enquiry_form_is_not_indexed(): void
    {
        $this->get('/inquiry')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', escape: false);
    }

    public function test_the_sitemap_lists_published_tours_only(): void
    {
        $this->tour(['slug' => 'listed', 'name' => 'Listed Trip']);
        $this->tour(['slug' => 'hidden', 'name' => 'Hidden Trip', 'is_published' => false]);

        $response = $this->get('/sitemap.xml')->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');

        $xml = $response->getContent();

        $this->assertStringContainsString(url('/'), $xml);
        $this->assertStringContainsString('/tours/listed', $xml);
        $this->assertStringNotContainsString('/tours/hidden', $xml);

        // Must be well-formed or search engines reject the whole file.
        $this->assertNotFalse(simplexml_load_string($xml), 'Sitemap must be valid XML');
    }

    public function test_robots_txt_points_at_the_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Sitemap:', $robots);
        $this->assertStringContainsString('Disallow: /admin', $robots);
    }

    public function test_social_profiles_render_and_are_declared_as_sameAs(): void
    {
        config(['site.social' => [
            'instagram' => 'https://www.instagram.com/twinsafricantravel/',
            'facebook' => 'https://www.facebook.com/TwinsAfricanTravel',
        ]]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('https://www.instagram.com/twinsafricantravel/', $html);
        $this->assertStringContainsString('https://www.facebook.com/TwinsAfricanTravel', $html);

        // Outbound profile links should not pass link equity.
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $html);

        $sameAs = collect($this->schema($html))
            ->firstWhere('@type', 'TravelAgency')['sameAs'] ?? [];

        $this->assertContains('https://www.instagram.com/twinsafricantravel/', $sameAs);
    }

    public function test_the_social_section_is_skipped_when_no_profiles_are_set(): void
    {
        config(['site.social' => []]);

        $this->get('/')->assertOk()->assertDontSee('Follow the journey');
    }

    public function test_the_award_badge_sits_below_the_reviews(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString(config('site.awards.0.image'), $html);
        $this->assertStringContainsString('https://www.tripadvisor.com/Profile/TwinsAfricanTravel', $html);

        // Order matters: the badge is social proof for the reviews above it.
        $this->assertLessThan(
            strpos($html, config('site.awards.0.image')),
            strpos($html, 'Voices from the trail'),
            'The award badge should render after the reviews section.'
        );
    }

    public function test_the_award_badge_file_exists(): void
    {
        foreach (config('site.awards') as $award) {
            $this->assertFileExists(public_path(ltrim($award['image'], '/')), $award['name'] . ' image is missing');
        }
    }

    public function test_the_awards_section_is_skipped_when_none_are_set(): void
    {
        $image = config('site.awards.0.image');

        config(['site.awards' => []]);

        $this->get('/')->assertOk()->assertDontSee($image, false);
    }

    public function test_the_share_image_and_favicons_exist(): void
    {
        foreach ([
            'assets/social/og-default.jpg' => [1200, 630],
            'apple-touch-icon.png' => [180, 180],
            'favicon-32x32.png' => [32, 32],
        ] as $file => [$width, $height]) {
            $path = public_path($file);

            $this->assertFileExists($path);
            $this->assertSame([$width, $height], array_slice(getimagesize($path), 0, 2), "{$file} is the wrong size");
        }
    }

    // --- Structured data graph ---------------------------------------------

    /** @return array<int, array<string, mixed>> */
    private function jsonLd(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return array_map(function (string $json) {
            $decoded = json_decode(trim($json), true);

            $this->assertNotNull($decoded, 'A JSON-LD block on the page is not valid JSON.');

            return $decoded;
        }, $matches[1]);
    }

    /** @param array<int, array<string, mixed>> $blocks */
    private function graphNode(array $blocks, string $type): ?array
    {
        foreach ($blocks as $block) {
            foreach ($block['@graph'] ?? [] as $node) {
                if (($node['@type'] ?? null) === $type) {
                    return $node;
                }
            }
        }

        return null;
    }

    public function test_the_organisation_and_website_share_one_graph(): void
    {
        /*
         * Two unrelated blocks make a crawler guess that the business and the
         * site belong together. One @graph with cross-referenced @ids says so.
         */
        $blocks = $this->jsonLd('/');

        $graphs = array_filter($blocks, fn (array $b) => isset($b['@graph']));

        $this->assertCount(1, $graphs, 'expected exactly one @graph block');

        $organisation = $this->graphNode($blocks, config('seo.organisation.type'));
        $website = $this->graphNode($blocks, 'WebSite');

        $this->assertNotNull($organisation);
        $this->assertNotNull($website);

        // The link between them is the whole point.
        $this->assertSame($organisation['@id'], $website['publisher']['@id']);
    }

    public function test_the_organisation_carries_a_square_logo(): void
    {
        // Google wants at least 112px square for the result header.
        $organisation = $this->graphNode($this->jsonLd('/'), config('seo.organisation.type'));

        $this->assertSame('ImageObject', $organisation['logo']['@type']);
        $this->assertSame(512, $organisation['logo']['width']);
        $this->assertSame(512, $organisation['logo']['height']);
        $this->assertStringContainsString('favicon-512x512.png', $organisation['logo']['url']);

        $this->assertFileExists(public_path('favicon-512x512.png'));
    }

    public function test_the_website_node_names_the_bare_domain(): void
    {
        // What Google usually prints above the result.
        $website = $this->graphNode($this->jsonLd('/'), 'WebSite');

        $this->assertSame(config('seo.organisation.name'), $website['name']);
        $this->assertSame(parse_url(url('/'), PHP_URL_HOST), $website['alternateName']);
    }

    public function test_the_rating_is_published_once_there_are_reviews(): void
    {
        \App\Models\Review::create([
            'name' => 'Amina', 'email' => 'a@example.com', 'rating' => 5,
            'body' => 'Superb trip from start to finish.', 'is_published' => true,
        ]);

        $organisation = $this->graphNode($this->jsonLd('/'), config('seo.organisation.type'));

        $this->assertSame(1, $organisation['aggregateRating']['reviewCount']);
        $this->assertSame(5, (int) $organisation['aggregateRating']['ratingValue']);
    }

    public function test_no_rating_is_claimed_when_there_are_no_reviews(): void
    {
        // An aggregateRating with a zero count is a structured-data error, not
        // a neutral statement.
        $organisation = $this->graphNode($this->jsonLd('/'), config('seo.organisation.type'));

        $this->assertArrayNotHasKey('aggregateRating', $organisation);
    }

    public function test_an_unpublished_review_does_not_inflate_the_rating(): void
    {
        \App\Models\Review::create([
            'name' => 'Held back', 'email' => 'h@example.com', 'rating' => 1,
            'body' => 'Awaiting moderation.', 'is_published' => false,
        ]);

        $organisation = $this->graphNode($this->jsonLd('/'), config('seo.organisation.type'));

        $this->assertArrayNotHasKey('aggregateRating', $organisation);
    }

    // --- Breadcrumbs --------------------------------------------------------

    /** @param array<int, array<string, mixed>> $blocks */
    private function breadcrumb(array $blocks): ?array
    {
        foreach ($blocks as $block) {
            if (($block['@type'] ?? null) === 'BreadcrumbList') {
                return $block;
            }
        }

        return null;
    }

    public function test_inner_pages_carry_a_breadcrumb_trail(): void
    {
        \App\Models\Page::create([
            'slug' => \App\Models\Page::ABOUT, 'title' => 'About Us',
            'sections' => [], 'is_published' => true,
        ]);

        foreach (['/tours', '/reviews', '/inquiry', '/about'] as $path) {
            $crumb = $this->breadcrumb($this->jsonLd($path));

            $this->assertNotNull($crumb, "no BreadcrumbList on {$path}");
            $this->assertSame('Home', $crumb['itemListElement'][0]['name']);
        }
    }

    public function test_breadcrumb_positions_run_without_a_gap(): void
    {
        // A break in the sequence invalidates the whole list.
        \App\Models\Page::create([
            'slug' => \App\Models\Page::TEAM, 'title' => 'Our Team',
            'sections' => [], 'is_published' => true,
        ]);
        \App\Models\Page::create([
            'slug' => \App\Models\Page::ABOUT, 'title' => 'About Us',
            'sections' => [], 'is_published' => true,
        ]);

        $crumb = $this->breadcrumb($this->jsonLd('/about/team'));

        $positions = array_column($crumb['itemListElement'], 'position');

        $this->assertSame([1, 2, 3], $positions);
        $this->assertSame(['Home', 'About Us', 'Our Team'], array_column($crumb['itemListElement'], 'name'));
    }

    public function test_the_home_page_has_no_breadcrumb(): void
    {
        // A trail of one entry pointing at itself says nothing.
        $this->assertNull($this->breadcrumb($this->jsonLd('/')));
    }

    public function test_every_breadcrumb_item_has_an_absolute_url(): void
    {
        $crumb = $this->breadcrumb($this->jsonLd('/tours'));

        foreach ($crumb['itemListElement'] as $item) {
            $this->assertStringStartsWith('http', $item['item'], 'breadcrumb urls must be absolute');
        }
    }

}
