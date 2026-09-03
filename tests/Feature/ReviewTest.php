<?php

namespace Tests\Feature;

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function review(array $overrides = []): Review
    {
        return Review::create(array_merge([
            'name' => 'Amara Okafor',
            'location' => 'Nairobi, Kenya',
            'body' => 'Our guide read the plains like a map and we were parked before the herd arrived.',
            'rating' => 5,
            'is_published' => true,
            'travelled_on' => now()->subMonths(3),
        ], $overrides));
    }

    private function tour(): Tour
    {
        return Tour::create([
            'slug' => 'migration', 'name' => 'Great Migration Safari', 'category' => 'Wildlife',
            'difficulty' => 'Easy', 'image' => 'tours/x.jpg', 'days' => '7 Days',
            'price' => '$2,450', 'rating' => 4.9, 'reviews' => 10,
        ]);
    }

    public function test_the_reviews_page_shows_published_reviews(): void
    {
        $this->review(['name' => 'Visible Person']);
        $this->review(['name' => 'Hidden Person', 'is_published' => false]);

        $this->get('/reviews')
            ->assertOk()
            ->assertSee('Visible Person')
            ->assertDontSee('Hidden Person');
    }

    public function test_the_summary_averages_only_published_reviews(): void
    {
        $this->review(['rating' => 5]);
        $this->review(['rating' => 4]);
        $this->review(['rating' => 1, 'is_published' => false]);

        $summary = Review::summary();

        $this->assertSame(2, $summary['total']);
        $this->assertSame(4.5, $summary['average']);
    }

    public function test_the_summary_is_blank_rather_than_zero_on_a_new_site(): void
    {
        // "0.0 out of 5" would read as a terrible score rather than no data.
        $summary = Review::summary();

        $this->assertNull($summary['average']);
        $this->assertSame(0, $summary['total']);
        $this->assertSame([5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0], $summary['distribution']);

        $this->get('/reviews')->assertOk()->assertSee('No reviews published yet');
    }

    public function test_category_averages_ignore_reviews_that_skipped_them(): void
    {
        $this->review(['rating_guiding' => 5]);
        $this->review(['rating_guiding' => null]);

        $this->assertSame(5.0, Review::summary()['guiding']);
    }

    public function test_a_visitor_can_submit_a_review(): void
    {
        $this->tour();

        $this->post('/reviews', [
            'name' => 'Sam Walker',
            'email' => 'sam@example.com',
            'location' => 'Leeds, UK',
            'tour_slug' => 'migration',
            'rating' => 5,
            'title' => 'Unforgettable',
            'body' => 'The crater at first light was the most extraordinary place I have stood.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'name' => 'Sam Walker',
            'tour_slug' => 'migration',
            'tour_name' => 'Great Migration Safari',
        ]);
    }

    public function test_a_submitted_review_is_held_for_moderation(): void
    {
        // Publishing unread submissions on a live site invites spam and abuse.
        $this->post('/reviews', [
            'name' => 'Sam Walker',
            'email' => 'sam@example.com',
            'rating' => 5,
            'body' => 'The crater at first light was the most extraordinary place I have stood.',
        ]);

        $review = Review::first();

        $this->assertFalse($review->is_published);
        $this->get('/reviews')->assertOk()->assertDontSee('Sam Walker');
    }

    public function test_the_form_thanks_the_visitor(): void
    {
        $this->followingRedirects()->post('/reviews', [
            'name' => 'Sam Walker',
            'email' => 'sam@example.com',
            'rating' => 5,
            'body' => 'The crater at first light was the most extraordinary place I have stood.',
        ])->assertOk()->assertSee('sent for checking');
    }

    public function test_a_photo_can_be_attached(): void
    {
        Storage::fake('public');

        $this->post('/reviews', [
            'name' => 'Sam Walker',
            'email' => 'sam@example.com',
            'rating' => 5,
            'body' => 'The crater at first light was the most extraordinary place I have stood.',
            'photo' => UploadedFile::fake()->image('me.jpg'),
        ])->assertRedirect();

        $review = Review::first();

        $this->assertNotNull($review->photo);
        Storage::disk('public')->assertExists($review->photo);
        $this->assertStringStartsWith('/storage/', $review->photo_url);
    }

    public function test_invalid_submissions_are_rejected(): void
    {
        $this->from('/reviews')->post('/reviews', [
            'name' => '',
            'email' => 'nope',
            'rating' => 9,
            'body' => 'Too short',
        ])->assertSessionHasErrors(['name', 'email', 'rating', 'body'], null, 'review');

        $this->assertSame(0, Review::count());
    }

    public function test_the_honeypot_blocks_bots(): void
    {
        $this->from('/reviews')->post('/reviews', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'rating' => 5,
            'body' => 'Buy cheap things at this definitely legitimate website.',
            'website' => 'http://spam.example',
        ]);

        $this->assertSame(0, Review::count());
    }

    public function test_a_future_travel_date_is_rejected(): void
    {
        $this->from('/reviews')->post('/reviews', [
            'name' => 'Sam Walker',
            'email' => 'sam@example.com',
            'rating' => 5,
            'body' => 'The crater at first light was the most extraordinary place I have stood.',
            'travelled_on' => now()->addYear()->toDateString(),
        ])->assertSessionHasErrors('travelled_on', null, 'review');
    }

    public function test_initials_stand_in_for_a_missing_photo(): void
    {
        $this->assertSame('AO', $this->review()->initials);
        $this->assertSame('DC', $this->review(['name' => 'David Chen'])->initials);
    }

    public function test_featured_reviews_appear_on_the_homepage(): void
    {
        $this->review(['name' => 'Featured Traveller', 'is_featured' => true]);

        $this->get('/')->assertOk()->assertSee('Featured Traveller');
    }

    public function test_the_admin_can_approve_a_review(): void
    {
        $review = $this->review(['is_published' => false]);

        Livewire::actingAs($this->admin())
            ->test(ListReviews::class)
            ->callTableAction('approve', $review)
            ->assertHasNoTableActionErrors();

        $this->assertTrue($review->fresh()->is_published);
    }

    public function test_approve_is_hidden_once_published(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListReviews::class)
            ->assertTableActionHidden('approve', $this->review());
    }

    public function test_unpublishing_also_removes_it_from_the_homepage(): void
    {
        // A hidden review must not keep a featured slot it can no longer fill.
        $review = $this->review(['is_featured' => true]);

        Livewire::actingAs($this->admin())
            ->test(ListReviews::class)
            ->callTableAction('unpublish', $review);

        $review->refresh();

        $this->assertFalse($review->is_published);
        $this->assertFalse($review->is_featured);
    }

    public function test_the_form_is_a_modal_with_a_trigger(): void
    {
        $html = $this->get('/reviews')->assertOk()->getContent();

        $this->assertStringContainsString('data-open-review', $html, 'Needs a button to open the modal');
        $this->assertStringContainsString('<dialog', $html, 'Form should live in a native dialog');
        $this->assertStringContainsString('id="review-dialog"', $html);
    }

    public function test_the_star_picker_offers_five_required_options(): void
    {
        $html = $this->get('/reviews')->assertOk()->getContent();

        // Radios, so a rating can be chosen with no JavaScript at all.
        foreach (range(1, 5) as $star) {
            $this->assertStringContainsString('value="' . $star . '"', $html);
            $this->assertStringContainsString('id="rv-star-' . $star . '"', $html);
        }
    }

    public function test_the_modal_reopens_when_the_submission_is_rejected(): void
    {
        // Otherwise the visitor is bounced to a silent page with their errors
        // hidden inside a closed dialog.
        $html = $this->from('/reviews')
            ->followingRedirects()
            ->post('/reviews', ['name' => '', 'email' => 'nope', 'rating' => 9, 'body' => 'short'])
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-open-on-load', $html);
    }

    public function test_the_modal_keeps_what_was_typed_after_a_rejection(): void
    {
        $html = $this->from('/reviews')
            ->followingRedirects()
            ->post('/reviews', [
                'name' => 'Sam Walker',
                'email' => 'not-an-email',
                'rating' => 4,
                'body' => 'A perfectly reasonable review body that is long enough.',
            ])
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('value="Sam Walker"', $html);
        $this->assertStringContainsString('A perfectly reasonable review body', $html);
    }

    public function test_the_reviews_page_publishes_rating_schema(): void
    {
        $this->review();

        $html = $this->get('/reviews')->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        $blocks = array_map(fn ($json) => json_decode(trim($json), true), $matches[1]);
        $rated = collect($blocks)->first(fn ($block) => isset($block['aggregateRating']));

        $this->assertNotNull($rated, 'Reviews page should carry aggregate rating schema');
        $this->assertSame(1, $rated['aggregateRating']['reviewCount']);
    }
}
