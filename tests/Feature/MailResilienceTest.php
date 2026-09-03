<?php

namespace Tests\Feature;

use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Mockery;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

/**
 * The mail server is the least reliable part of the stack — DNS blips, rate
 * limits and rejected addresses are routine. None of them should be able to
 * take down a page.
 */
class MailResilienceTest extends TestCase
{
    use RefreshDatabase;

    /** Make every send throw the way an unreachable SMTP host does. */
    private function breakTheMailer(): void
    {
        $pending = Mockery::mock(PendingMail::class);
        $pending->shouldReceive('queue')->andThrow(new TransportException(
            'Connection could not be established with host "smtp.gmail.com:587"'
        ));
        $pending->shouldReceive('send')->andThrow(new TransportException('unreachable'));

        Mail::shouldReceive('to')->andReturn($pending);
    }

    public function test_a_subscription_still_succeeds_when_mail_fails(): void
    {
        $this->breakTheMailer();

        $this->post('/subscribe', ['email' => 'reader@example.com'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        // The sign-up is what matters and it is already saved.
        $this->assertDatabaseHas('subscribers', ['email' => 'reader@example.com']);
    }

    public function test_the_visitor_still_sees_the_confirmation_when_mail_fails(): void
    {
        $this->breakTheMailer();

        $this->followingRedirects()
            ->post('/subscribe', ['email' => 'reader@example.com'])
            ->assertOk()
            ->assertSee('on the list', false);
    }

    public function test_a_failed_welcome_email_is_logged(): void
    {
        Log::spy();
        $this->breakTheMailer();

        $this->post('/subscribe', ['email' => 'reader@example.com']);

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message) => str_contains($message, 'Welcome email'));
    }

    public function test_the_newsletter_action_survives_a_dead_mail_server(): void
    {
        Subscriber::create(['email' => 'one@example.com', 'subscribed_at' => now()]);
        Subscriber::create(['email' => 'two@example.com', 'subscribed_at' => now()]);

        $this->breakTheMailer();

        // Previously this threw straight out of the action and the panel
        // rendered a 500 error page.
        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(ListSubscribers::class)
            ->callAction('sendNewsletter', ['subject' => 'News'])
            ->assertHasNoActionErrors()
            ->assertNotified();
    }

    public function test_one_bad_address_does_not_abandon_the_rest_of_the_list(): void
    {
        Subscriber::create(['email' => 'good@example.com', 'subscribed_at' => now()]);
        Subscriber::create(['email' => 'bad@example.com', 'subscribed_at' => now()]);
        Subscriber::create(['email' => 'alsogood@example.com', 'subscribed_at' => now()]);

        $attempted = [];

        $pending = Mockery::mock(PendingMail::class);
        $pending->shouldReceive('queue')->andReturnUsing(function () use (&$attempted) {
            // The middle address is rejected; the others must still go.
            if (end($attempted) === 'bad@example.com') {
                throw new TransportException('550 mailbox unavailable');
            }

            return null;
        });

        Mail::shouldReceive('to')->andReturnUsing(function (string $email) use ($pending, &$attempted) {
            $attempted[] = $email;

            return $pending;
        });

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(ListSubscribers::class)
            ->callAction('sendNewsletter', ['subject' => 'News'])
            ->assertHasNoActionErrors();

        $this->assertCount(3, $attempted, 'Every subscriber should have been attempted');
    }

    public function test_smtp_has_a_bounded_timeout(): void
    {
        // Without one, a stalled connection holds the request open indefinitely.
        $this->assertIsInt(config('mail.mailers.smtp.timeout'));
        $this->assertGreaterThan(0, config('mail.mailers.smtp.timeout'));
    }
}
