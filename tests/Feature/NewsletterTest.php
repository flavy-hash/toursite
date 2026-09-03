<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_someone_can_subscribe(): void
    {
        $this->post('/subscribe', ['email' => 'reader@example.com'])
            ->assertRedirect();

        $this->assertDatabaseHas('subscribers', [
            'email' => 'reader@example.com',
            'source' => 'newsletter',
            'unsubscribed_at' => null,
        ]);

        $this->assertNotNull(Subscriber::first()->subscribed_at);
    }

    public function test_the_form_confirms_after_signing_up(): void
    {
        $this->followingRedirects()
            ->post('/subscribe', ['email' => 'reader@example.com'])
            ->assertOk()
            ->assertSee('on the list', false);
    }

    public function test_addresses_are_stored_lowercase_and_trimmed(): void
    {
        $this->post('/subscribe', ['email' => '  Reader@Example.COM  ']);

        $this->assertDatabaseHas('subscribers', ['email' => 'reader@example.com']);
    }

    public function test_signing_up_twice_does_not_duplicate(): void
    {
        $this->post('/subscribe', ['email' => 'reader@example.com']);
        $this->post('/subscribe', ['email' => 'reader@example.com']);

        $this->assertSame(1, Subscriber::where('email', 'reader@example.com')->count());
    }

    public function test_the_response_is_identical_for_a_new_and_an_existing_address(): void
    {
        // Otherwise the form becomes a way to test who is on the list.
        $first = $this->followingRedirects()->post('/subscribe', ['email' => 'reader@example.com'])->getContent();
        $second = $this->followingRedirects()->post('/subscribe', ['email' => 'reader@example.com'])->getContent();

        $this->assertStringContainsString('on the list', $first);
        $this->assertStringContainsString('on the list', $second);
    }

    public function test_resubscribing_reinstates_someone_who_opted_out(): void
    {
        Subscriber::create([
            'email' => 'reader@example.com',
            'subscribed_at' => now()->subYear(),
            'unsubscribed_at' => now()->subMonth(),
        ]);

        $this->post('/subscribe', ['email' => 'reader@example.com']);

        $this->assertNull(Subscriber::first()->unsubscribed_at);
    }

    public function test_an_invalid_address_is_rejected(): void
    {
        $this->from('/')->post('/subscribe', ['email' => 'not-an-email'])
            ->assertRedirect('/')
            ->assertSessionHasErrors('email', null, 'newsletter');

        $this->assertSame(0, Subscriber::count());
    }

    public function test_the_honeypot_blocks_bots(): void
    {
        $this->from('/')->post('/subscribe', [
            'email' => 'bot@example.com',
            'website' => 'http://spam.example',
        ])->assertRedirect('/');

        $this->assertSame(0, Subscriber::count());
    }

    public function test_newsletter_errors_do_not_leak_into_other_forms(): void
    {
        // The homepage carries this form; the booking form must stay clean.
        $this->from('/')->post('/subscribe', ['email' => 'nope'])
            ->assertSessionHasErrors('email', null, 'newsletter')
            ->assertSessionDoesntHaveErrors('email');
    }

    public function test_the_admin_can_see_subscribers(): void
    {
        Subscriber::create(['email' => 'reader@example.com', 'subscribed_at' => now()]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin/subscribers')
            ->assertOk()
            ->assertSee('reader@example.com');
    }

    public function test_subscribers_cannot_be_created_from_the_panel(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin/subscribers/create')
            ->assertNotFound();
    }
}
