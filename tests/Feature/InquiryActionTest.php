<?php

namespace Tests\Feature;

use App\Filament\Resources\Inquiries\Pages\EditInquiry;
use App\Filament\Resources\Inquiries\Pages\ListInquiries;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InquiryActionTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_confirm_marks_an_enquiry_as_booked(): void
    {
        $inquiry = $this->enquiry();

        $this->assertSame(Inquiry::NEW, $inquiry->status);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry)
            ->assertHasNoTableActionErrors();

        $this->assertSame(Inquiry::BOOKED, $inquiry->fresh()->status);
    }

    public function test_confirm_is_hidden_once_already_booked(): void
    {
        // Nothing to confirm twice, and it stops a stray click reopening work.
        $booked = $this->enquiry(['status' => Inquiry::BOOKED]);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->assertTableActionHidden('confirm', $booked);
    }

    public function test_confirm_is_visible_on_a_new_enquiry(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->assertTableActionVisible('confirm', $this->enquiry());
    }

    public function test_the_other_status_actions_move_an_enquiry_along(): void
    {
        $inquiry = $this->enquiry();

        foreach ([
            'status_contacted' => Inquiry::CONTACTED,
            'status_quoted' => Inquiry::QUOTED,
            'status_closed' => Inquiry::CLOSED,
            'status_new' => Inquiry::NEW,
        ] as $action => $expected) {
            Livewire::actingAs($this->admin())
                ->test(ListInquiries::class)
                ->callTableAction($action, $inquiry);

            $this->assertSame($expected, $inquiry->fresh()->status, "{$action} should set {$expected}");
        }
    }

    public function test_a_status_action_hides_itself_when_already_in_that_state(): void
    {
        $contacted = $this->enquiry(['status' => Inquiry::CONTACTED]);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->assertTableActionHidden('status_contacted', $contacted);
    }

    public function test_several_enquiries_can_be_confirmed_at_once(): void
    {
        $one = $this->enquiry(['email' => 'a@example.com']);
        $two = $this->enquiry(['email' => 'b@example.com']);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableBulkAction('bulk_booked', [$one, $two]);

        $this->assertSame(Inquiry::BOOKED, $one->fresh()->status);
        $this->assertSame(Inquiry::BOOKED, $two->fresh()->status);
    }

    public function test_the_edit_screen_can_confirm_too(): void
    {
        $inquiry = $this->enquiry();

        Livewire::actingAs($this->admin())
            ->test(EditInquiry::class, ['record' => $inquiry->getKey()])
            ->callAction('confirm')
            ->assertHasNoActionErrors();

        $this->assertSame(Inquiry::BOOKED, $inquiry->fresh()->status);
    }

    public function test_confirming_does_not_disturb_the_submitted_details(): void
    {
        // The record must stay a faithful copy of what the visitor sent.
        $inquiry = $this->enquiry(['message' => 'Hoping to add Zanzibar.']);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry);

        $fresh = $inquiry->fresh();

        $this->assertSame('Jane Traveller', $fresh->name);
        $this->assertSame('jane@example.com', $fresh->email);
        $this->assertSame('Hoping to add Zanzibar.', $fresh->message);
        $this->assertSame('Great Migration Safari', $fresh->tour_name);
    }

    public function test_an_enquiry_can_be_viewed_without_editing_it(): void
    {
        $inquiry = $this->enquiry(['message' => 'Hoping to add Zanzibar afterwards.']);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->assertTableActionVisible('view', $inquiry)
            ->callTableAction('view', $inquiry)
            ->assertHasNoTableActionErrors();
    }

    public function test_the_view_shows_everything_the_visitor_sent(): void
    {
        $inquiry = $this->enquiry([
            'phone' => '+255 754 332 741',
            'message' => 'Hoping to add Zanzibar afterwards.',
            'travellers' => 4,
        ]);

        $this->actingAs($this->admin())
            ->get("/admin/inquiries/{$inquiry->id}")
            ->assertOk()
            ->assertSee('Jane Traveller')
            ->assertSee('jane@example.com')
            ->assertSee('+255 754 332 741')
            ->assertSee('Hoping to add Zanzibar afterwards.')
            ->assertSee('Great Migration Safari');
    }

    public function test_the_view_page_offers_a_reply_link(): void
    {
        $inquiry = $this->enquiry();

        $this->actingAs($this->admin())
            ->get("/admin/inquiries/{$inquiry->id}")
            ->assertOk()
            ->assertSee('mailto:jane@example.com', false);
    }

    public function test_the_view_page_needs_an_admin(): void
    {
        $inquiry = $this->enquiry();

        $this->get("/admin/inquiries/{$inquiry->id}")->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get("/admin/inquiries/{$inquiry->id}")
            ->assertForbidden();
    }

    public function test_viewing_does_not_change_the_enquiry(): void
    {
        // A read-only view must not quietly touch the record.
        $inquiry = $this->enquiry();
        $before = $inquiry->updated_at;

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('view', $inquiry);

        $fresh = $inquiry->fresh();

        $this->assertSame(Inquiry::NEW, $fresh->status);
        $this->assertEquals($before, $fresh->updated_at);
    }

    public function test_the_dashboard_counts_follow_the_actions(): void
    {
        $inquiry = $this->enquiry();

        $this->assertSame(1, Inquiry::awaiting()->count());

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->callTableAction('confirm', $inquiry);

        $this->assertSame(0, Inquiry::awaiting()->count());
        $this->assertSame(1, Inquiry::where('status', Inquiry::BOOKED)->count());
    }
}
