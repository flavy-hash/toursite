<?php

namespace Tests\Feature;

use App\Filament\Resources\Inquiries\Pages\EditInquiry;
use App\Filament\Resources\Inquiries\Pages\ListInquiries;
use App\Filament\Resources\Inquiries\Pages\ViewInquiry;
use App\Mail\BookingConfirmed;
use App\Models\Inquiry;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class BookingConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function enquiry(array $overrides = []): Inquiry
    {
        return Inquiry::create(array_merge([
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'travellers' => 2,
            'tour_name' => 'Great Migration Safari',
        ], $overrides));
    }

    public function test_confirming_emails_the_guest(): void
    {
        $inquiry = $this->enquiry();

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry);

        Mail::assertQueued(
            BookingConfirmed::class,
            fn (BookingConfirmed $mail) => $mail->hasTo('jane@example.com')
                && $mail->inquiry->is($inquiry)
        );
    }

    public function test_the_send_is_recorded_on_the_enquiry(): void
    {
        // Staff need to see that the guest was told, not just that the status
        // changed, so the moment of sending is stamped on the record.
        $inquiry = $this->enquiry();

        $this->assertNull($inquiry->confirmation_sent_at);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry);

        $this->assertNotNull($inquiry->fresh()->confirmation_sent_at);
        $this->assertFalse($inquiry->fresh()->awaitsConfirmationEmail());
    }

    public function test_the_edit_screen_confirm_also_emails(): void
    {
        $inquiry = $this->enquiry();

        Livewire::actingAs($this->admin())
            ->test(EditInquiry::class, ['record' => $inquiry->getKey()])
            ->callAction('confirm');

        Mail::assertQueued(BookingConfirmed::class);
        $this->assertNotNull($inquiry->fresh()->confirmation_sent_at);
    }

    public function test_the_view_screen_confirm_also_emails(): void
    {
        $inquiry = $this->enquiry();

        Livewire::actingAs($this->admin())
            ->test(ViewInquiry::class, ['record' => $inquiry->getKey()])
            ->callAction('confirm');

        Mail::assertQueued(BookingConfirmed::class);
        $this->assertNotNull($inquiry->fresh()->confirmation_sent_at);
    }

    public function test_resend_is_available_from_the_view_screen(): void
    {
        $inquiry = $this->enquiry(['status' => Inquiry::BOOKED]);
        $inquiry->forceFill(['confirmation_sent_at' => now()])->saveQuietly();

        Livewire::actingAs($this->admin())
            ->test(ViewInquiry::class, ['record' => $inquiry->getKey()])
            ->callAction('resend_confirmation')
            ->assertHasNoActionErrors();

        Mail::assertQueued(BookingConfirmed::class, 1);
    }

    public function test_a_bulk_confirm_emails_every_guest(): void
    {
        $one = $this->enquiry(['email' => 'a@example.com']);
        $two = $this->enquiry(['email' => 'b@example.com']);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableBulkAction('bulk_booked', [$one, $two]);

        Mail::assertQueued(BookingConfirmed::class, 2);
        $this->assertNotNull($one->fresh()->confirmation_sent_at);
        $this->assertNotNull($two->fresh()->confirmation_sent_at);
    }

    public function test_setting_the_status_through_the_form_emails_too(): void
    {
        // The status field on the edit form is another way into "booked", and
        // it must not be a route that silently skips telling the guest.
        $inquiry = $this->enquiry();

        Livewire::actingAs($this->admin())
            ->test(EditInquiry::class, ['record' => $inquiry->getKey()])
            ->fillForm(['status' => Inquiry::BOOKED])
            ->call('save');

        Mail::assertQueued(BookingConfirmed::class);
    }

    public function test_only_booking_sends_the_email(): void
    {
        $inquiry = $this->enquiry();

        foreach (['status_contacted', 'status_quoted', 'status_closed'] as $action) {
            Livewire::actingAs($this->admin())
                ->test(ListInquiries::class)
                ->callTableAction($action, $inquiry);
        }

        Mail::assertNothingQueued();
    }

    public function test_editing_a_booked_enquiry_does_not_resend(): void
    {
        $inquiry = $this->enquiry();

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry);

        Mail::assertQueued(BookingConfirmed::class, 1);

        // Correcting a typo in the phone number must not re-congratulate them.
        $inquiry->fresh()->update(['phone' => '+255 700 000 000']);

        Mail::assertQueued(BookingConfirmed::class, 1);
    }

    public function test_reopening_and_reconfirming_does_not_send_twice(): void
    {
        $inquiry = $this->enquiry();

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('status_new', $inquiry);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry->fresh());

        Mail::assertQueued(BookingConfirmed::class, 1);
    }

    public function test_resend_sends_again_on_request(): void
    {
        // The one deliberate way past the send-once rule, for a mistyped
        // address or a first attempt that failed.
        $inquiry = $this->enquiry(['status' => Inquiry::BOOKED]);
        $inquiry->forceFill(['confirmation_sent_at' => now()])->saveQuietly();

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('resend_confirmation', $inquiry)
            ->assertHasNoTableActionErrors();

        Mail::assertQueued(BookingConfirmed::class, 1);
    }

    public function test_resend_is_hidden_until_the_booking_is_confirmed(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->assertTableActionHidden('resend_confirmation', $this->enquiry());
    }

    public function test_an_enquiry_with_no_email_is_still_bookable(): void
    {
        // Enquiries only ever arrive from the public form, which requires an
        // address, but a booking must never depend on the email going out.
        $inquiry = $this->enquiry(['email' => '']);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry)
            ->assertHasNoTableActionErrors();

        $this->assertSame(Inquiry::BOOKED, $inquiry->fresh()->status);
        Mail::assertNothingQueued();
    }

    public function test_the_email_carries_the_booking_details(): void
    {
        $tour = Tour::create([
            'slug' => 'great-migration-safari',
            'name' => 'Great Migration Safari',
            'category' => 'Wildlife',
            'difficulty' => 'Easy',
            'days' => '7 Days',
            'price' => '$2,400',
            'summary' => ['A paragraph.'],
            'highlights' => ['A highlight'],
            'itinerary' => [],
            'included' => [],
            'excluded' => [],
            'gallery' => [],
        ]);

        $inquiry = $this->enquiry([
            'tour_slug' => $tour->slug,
            'travel_date' => '2026-11-14',
            'travellers' => 4,
        ]);

        $rendered = (new BookingConfirmed($inquiry))->render();

        $this->assertStringContainsString('Jane Traveller', $rendered);
        $this->assertStringContainsString('Great Migration Safari', $rendered);
        $this->assertStringContainsString('14 November 2026', $rendered);
        $this->assertStringContainsString('TA-' . str_pad((string) $inquiry->id, 5, '0', STR_PAD_LEFT), $rendered);
    }

    public function test_the_email_is_transactional_and_offers_no_unsubscribe(): void
    {
        // Someone who has just booked must receive this whether or not they
        // take the newsletter, so offering to opt out would be misleading.
        $rendered = (new BookingConfirmed($this->enquiry()))->render();

        $this->assertStringNotContainsString('Unsubscribe', $rendered);
        $this->assertStringContainsString(config('site.contact.email'), $rendered);
    }

    public function test_the_header_carries_the_logo(): void
    {
        // Shared by every email, so this covers the welcome and newsletter too.
        $rendered = (new BookingConfirmed($this->enquiry()))->render();

        $this->assertFileExists(public_path('assets/images/logo-side.png'));

        /*
         * Absolute, whatever the host: an email has no page to resolve a
         * relative path against, so a root-relative src would show a broken
         * image in every inbox. The host itself comes from APP_URL.
         */
        $this->assertMatchesRegularExpression(
            '#<img src="https?://[^"]+/assets/images/logo-side\.png"#',
            $rendered
        );

        // Spaces and capitals in the filename would need URL-encoding and
        // break on a case-sensitive server, so the name must stay web-safe.
        $this->assertStringNotContainsString('logo twins', $rendered);
        $this->assertStringNotContainsString('%20', $rendered);
    }

    public function test_the_logo_falls_back_to_the_brand_name(): void
    {
        // Outlook blocks remote images by default, so the alt text is what a
        // good share of recipients actually see.
        $rendered = (new BookingConfirmed($this->enquiry()))->render();

        $this->assertStringContainsString(
            'alt="' . config('site.brand.name') . ' ' . config('site.brand.suffix') . '"',
            $rendered
        );
    }

    public function test_the_subject_names_the_trip(): void
    {
        $envelope = (new BookingConfirmed($this->enquiry()))->envelope();

        $this->assertSame('Your booking is confirmed — Great Migration Safari', $envelope->subject);
    }

    public function test_a_general_enquiry_still_gets_a_sensible_subject(): void
    {
        $envelope = (new BookingConfirmed($this->enquiry(['tour_name' => null])))->envelope();

        $this->assertSame('Your booking is confirmed — your trip', $envelope->subject);
    }
}
