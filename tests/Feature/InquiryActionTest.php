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
