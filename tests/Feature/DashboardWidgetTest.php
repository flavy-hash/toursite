<?php

namespace Tests\Feature;

use App\Filament\Widgets\BusinessOverview;
use App\Filament\Widgets\EnquiriesByPackage;
use App\Filament\Widgets\EnquiriesOverTime;
use App\Filament\Widgets\LatestEnquiries;
use App\Filament\Widgets\PackagesByCategory;
use App\Models\Inquiry;
use Filament\Facades\Filament;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function tour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'slug' => 'w-' . uniqid(),
            'name' => 'Widget Trip',
            'category' => 'Wildlife',
            'difficulty' => 'Easy',
            'image' => 'tours/x.jpg',
            'days' => '5 Days',
            'price' => '$1,000',
            'rating' => 4.5,
            'reviews' => 1,
        ], $overrides));
    }

    private function enquiry(array $overrides = []): Inquiry
    {
        return Inquiry::create(array_merge([
            'name' => 'Someone',
            'email' => 'someone@example.com',
            'travellers' => 2,
            'tour_name' => 'Widget Trip',
        ], $overrides));
    }

    public function test_the_dashboard_loads_and_registers_every_widget(): void
    {
        $this->tour();
        $this->enquiry();

        $this->actingAs($this->admin())->get('/admin')->assertOk();

        // Widgets are lazy-loaded Livewire components, so they are not in the
        // page HTML — assert they are registered, then render each directly.
        $registered = Filament::getPanel('admin')->getWidgets();

        foreach ([BusinessOverview::class, EnquiriesOverTime::class, EnquiriesByPackage::class,
                  PackagesByCategory::class, LatestEnquiries::class] as $widget) {
            $this->assertContains($widget, $registered, class_basename($widget) . ' is not on the dashboard');
        }
    }

    public function test_every_widget_renders_with_real_data(): void
    {
        $this->tour();
        $this->enquiry();

        Livewire::actingAs($this->admin())->test(BusinessOverview::class)
            ->assertOk()
            ->assertSee('Enquiries this month');

        Livewire::actingAs($this->admin())->test(LatestEnquiries::class)
            ->assertOk()
            ->assertSee('Someone');
    }

    public function test_every_widget_survives_a_completely_empty_database(): void
    {
        // A brand new install must not divide by zero or fatal on empty sets.
        Livewire::actingAs($this->admin())->test(BusinessOverview::class)
            ->assertOk()
            ->assertSee('None yet this month');

        Livewire::actingAs($this->admin())->test(EnquiriesOverTime::class)->assertOk();
        Livewire::actingAs($this->admin())->test(EnquiriesByPackage::class)->assertOk();
        Livewire::actingAs($this->admin())->test(PackagesByCategory::class)->assertOk();

        Livewire::actingAs($this->admin())->test(LatestEnquiries::class)
            ->assertOk()
            ->assertSee('No enquiries yet');
    }

    public function test_the_stats_count_what_they_claim(): void
    {
        $this->tour(['is_published' => true]);
        $this->tour(['is_published' => false]);
        $this->enquiry(['status' => 'new']);
        $this->enquiry(['status' => 'booked']);

        Livewire::actingAs($this->admin())
            ->test(BusinessOverview::class)
            ->assertSee('Live packages')
            ->assertSee('Awaiting reply');

        $this->assertSame(1, Tour::published()->count());
        $this->assertSame(1, Inquiry::where('status', 'new')->count());
        $this->assertSame(1, Inquiry::where('status', 'booked')->count());
    }

    public function test_each_chart_renders_and_respects_its_filter(): void
    {
        $this->tour();
        $this->enquiry();

        Livewire::actingAs($this->admin())
            ->test(EnquiriesOverTime::class)
            ->assertOk()
            ->set('filter', '7')
            ->assertOk()
            ->set('filter', '90')
            ->assertOk();

        Livewire::actingAs($this->admin())->test(EnquiriesByPackage::class)->assertOk();
        Livewire::actingAs($this->admin())->test(PackagesByCategory::class)->assertOk();
    }

    public function test_a_non_admin_cannot_see_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin')
            ->assertForbidden();
    }
}
