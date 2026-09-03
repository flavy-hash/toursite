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
}
