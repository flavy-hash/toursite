<?php

namespace Tests\Feature;

use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Mail\PackageNewsletter;
use App\Mail\SubscriberWelcome;
use App\Models\Subscriber;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterMailTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function tour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'slug' => 'migration-' . uniqid(),
            'name' => 'Great Migration Safari',
            'tagline' => 'Seven days with the herds',
            'category' => 'Wildlife',
            'difficulty' => 'Easy',
            'image' => 'tours/hero.jpg',
            'days' => '7 Days',
            'price' => '$2,450',
            'rating' => 4.9,
            'reviews' => 12,
        ], $overrides));
    }

    private function subscriber(array $overrides = []): Subscriber
    {
        return Subscriber::create(array_merge([
            'email' => 'reader@example.com',
            'subscribed_at' => now(),
        ], $overrides));
    }

    public function test_subscribing_sends_a_welcome_email(): void
    {
        Mail::fake();
        $this->tour();

        $this->post('/subscribe', ['email' => 'reader@example.com']);

        Mail::assertQueued(SubscriberWelcome::class, fn ($mail) => $mail->hasTo('reader@example.com'));
    }

    public function test_the_welcome_email_carries_packages_and_an_unsubscribe_link(): void
    {
        $this->tour();
        $subscriber = $this->subscriber();

        $body = (new SubscriberWelcome($subscriber))->render();

        $this->assertStringContainsString('Great Migration Safari', $body);
        $this->assertStringContainsString('$2,450', $body);
        $this->assertStringContainsString('/unsubscribe/' . $subscriber->id, $body);
    }

    public function test_a_rejected_signup_sends_nothing(): void
    {
        Mail::fake();

        $this->from('/')->post('/subscribe', ['email' => 'not-an-email']);

        Mail::assertNothingQueued();
    }

    public function test_the_admin_can_send_a_newsletter(): void
    {
        Mail::fake();

        $tour = $this->tour();
        $this->subscriber(['email' => 'one@example.com']);
        $this->subscriber(['email' => 'two@example.com']);

        Livewire::actingAs($this->admin())
            ->test(ListSubscribers::class)
            ->callAction('sendNewsletter', [
                'subject' => 'Dry season departures',
                'intro' => 'A few new dates.',
                'tours' => [$tour->id],
            ]);

        Mail::assertQueued(PackageNewsletter::class, 2);
        Mail::assertQueued(PackageNewsletter::class, fn ($mail) => $mail->hasTo('one@example.com'));
    }

    public function test_unsubscribed_addresses_are_skipped(): void
    {
        Mail::fake();

        $tour = $this->tour();
        $this->subscriber(['email' => 'active@example.com']);
        $this->subscriber(['email' => 'gone@example.com', 'unsubscribed_at' => now()]);

        Livewire::actingAs($this->admin())
            ->test(ListSubscribers::class)
            ->callAction('sendNewsletter', ['subject' => 'News', 'tours' => [$tour->id]]);

        Mail::assertQueued(PackageNewsletter::class, 1);
        Mail::assertNotQueued(PackageNewsletter::class, fn ($mail) => $mail->hasTo('gone@example.com'));
    }

    public function test_each_newsletter_is_addressed_individually(): void
    {
        // One message per subscriber, not a shared BCC — otherwise the
        // unsubscribe link could not be signed per address.
        Mail::fake();

        $this->subscriber(['email' => 'one@example.com']);
        $this->subscriber(['email' => 'two@example.com']);

        Livewire::actingAs($this->admin())
            ->test(ListSubscribers::class)
            ->callAction('sendNewsletter', ['subject' => 'News']);

        Mail::assertQueued(PackageNewsletter::class, function ($mail) {
            return count($mail->to) === 1;
        });
    }

    public function test_the_unsubscribe_link_opts_someone_out(): void
    {
        $subscriber = $this->subscriber();

        $this->get($subscriber->unsubscribeUrl())
            ->assertOk()
            ->assertSee('unsubscribed', false);

        $this->assertNotNull($subscriber->fresh()->unsubscribed_at);
        $this->assertFalse($subscriber->fresh()->is_subscribed);
    }

    public function test_an_unsigned_unsubscribe_link_is_rejected(): void
    {
        // Otherwise anyone could opt out a stranger by guessing an id.
        $subscriber = $this->subscriber();

        $this->get('/unsubscribe/' . $subscriber->id)->assertForbidden();

        $this->assertTrue($subscriber->fresh()->is_subscribed);
    }

    public function test_a_tampered_unsubscribe_link_is_rejected(): void
    {
        $one = $this->subscriber(['email' => 'one@example.com']);
        $two = $this->subscriber(['email' => 'two@example.com']);

        // Swap the id but keep the signature from the other subscriber.
        $tampered = str_replace('/unsubscribe/' . $one->id, '/unsubscribe/' . $two->id, $one->unsubscribeUrl());

        $this->get($tampered)->assertForbidden();

        $this->assertTrue($two->fresh()->is_subscribed);
    }

    public function test_unsubscribing_twice_is_harmless(): void
    {
        $subscriber = $this->subscriber();
        $url = $subscriber->unsubscribeUrl();

        $this->get($url)->assertOk();
        $first = $subscriber->fresh()->unsubscribed_at;

        $this->get($url)->assertOk()->assertSee('Already unsubscribed', false);

        // The original opt-out date is preserved rather than being reset.
        $this->assertEquals($first, $subscriber->fresh()->unsubscribed_at);
    }

    public function test_the_unsubscribe_link_does_not_expire(): void
    {
        // An old newsletter must still be able to opt someone out.
        $subscriber = $this->subscriber();
        $url = $subscriber->unsubscribeUrl();

        $this->travel(2)->years();

        $this->get($url)->assertOk();
        $this->assertFalse($subscriber->fresh()->is_subscribed);
    }

    public function test_the_unsubscribe_page_is_not_indexed(): void
    {
        $this->get($this->subscriber()->unsubscribeUrl())
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_the_send_action_is_hidden_with_no_subscribers(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListSubscribers::class)
            ->assertActionHidden('sendNewsletter');
    }
}
