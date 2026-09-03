<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function tour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'slug' => 'test-safari',
            'name' => 'Test Safari',
            'category' => 'Wildlife',
            'difficulty' => 'Easy',
            'image' => '/assets/images/carousel/lionss_with_her_cub.jpg',
            'days' => '5 Days',
            'price' => '$1,000',
            'rating' => 4.8,
            'reviews' => 10,
            'summary' => ['A paragraph.'],
            'highlights' => ['A highlight'],
            'itinerary' => [['day' => 1, 'title' => 'Arrive', 'copy' => 'Land.', 'stay' => 'Lodge', 'meals' => 'Dinner']],
            'included' => ['Park fees'],
            'excluded' => ['Flights'],
            'gallery' => [],
        ], $overrides));
    }

    public function test_admin_panel_requires_login(): void
    {
        $this->get('/admin/tours')->assertRedirect('/admin/login');
        $this->get('/admin/inquiries')->assertRedirect('/admin/login');
    }

    public function test_a_non_admin_account_cannot_reach_the_panel(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/tours')
            ->assertForbidden();
    }

    public function test_admin_can_list_tours(): void
    {
        $this->tour(['name' => 'Great Migration Safari']);

        $this->actingAs($this->admin())
            ->get('/admin/tours')
            ->assertOk()
            ->assertSee('Great Migration Safari');
    }

    public function test_the_listing_shows_a_thumbnail_for_each_tour(): void
    {
        // ImageColumn checks the file exists on the disk before emitting a URL,
        // so the thumbnail only proves out against a file that is really there.
        Storage::fake('public');
        Storage::disk('public')->put('tours/hero.jpg', 'binary');

        $this->tour(['image' => 'tours/hero.jpg']);

        // Filament leaves a state untouched only when it is a fully-qualified
        // URL, so the column must resolve the raw key through the public disk.
        $this->actingAs($this->admin())
            ->get('/admin/tours')
            ->assertOk()
            ->assertSee('/storage/tours/hero.jpg', escape: false);
    }

    public function test_admin_can_open_the_tour_create_and_edit_screens(): void
    {
        $tour = $this->tour();

        $this->actingAs($this->admin())->get('/admin/tours/create')->assertOk();
        $this->actingAs($this->admin())->get("/admin/tours/{$tour->id}/edit")->assertOk();
    }

    public function test_admin_can_list_and_edit_inquiries(): void
    {
        $inquiry = Inquiry::create([
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'travellers' => 2,
            'tour_slug' => 'test-safari',
            'tour_name' => 'Test Safari',
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/inquiries')
            ->assertOk()
            ->assertSee('Jane Traveller');

        $this->actingAs($this->admin())
            ->get("/admin/inquiries/{$inquiry->id}/edit")
            ->assertOk();
    }

    public function test_the_edit_screen_loads_the_existing_image_for_replacement(): void
    {
        $tour = $this->tour([
            'image' => 'tours/hero.jpg',
            'gallery' => ['tours/gallery/a.jpg', 'tours/gallery/b.jpg'],
        ]);

        // Filament can only preview and replace files it can find on the
        // upload disk, so the stored value must be a disk key, never a
        // /public path.
        $this->assertStringStartsNotWith('/', $tour->image);

        foreach ($tour->gallery as $path) {
            $this->assertStringStartsNotWith('/', $path);
        }

        $this->actingAs($this->admin())
            ->get("/admin/tours/{$tour->id}/edit")
            ->assertOk()
            // The labelled panel and its Replace action must actually render;
            // a bad icon or action namespace only fails at render time.
            ->assertSee('Hero photo')
            ->assertSee('Replace')
            ->assertSee('Gallery');
    }

    public function test_inquiries_cannot_be_created_from_the_panel(): void
    {
        // Enquiries only ever arrive from the public form.
        $this->actingAs($this->admin())->get('/admin/inquiries/create')->assertNotFound();
    }

    public function test_a_new_tour_appears_on_the_public_site(): void
    {
        $this->tour(['slug' => 'brand-new-trip', 'name' => 'Brand New Trip']);

        $this->get('/tours')->assertOk()->assertSee('Brand New Trip');
        $this->get('/tours/brand-new-trip')->assertOk()->assertSee('Brand New Trip');
    }

    public function test_an_unpublished_tour_is_hidden_from_the_public_site(): void
    {
        $this->tour(['slug' => 'draft-trip', 'name' => 'Draft Trip', 'is_published' => false]);

        $this->get('/tours')->assertOk()->assertDontSee('Draft Trip');
        $this->get('/tours/draft-trip')->assertNotFound();
    }

    public function test_the_public_listing_filters_by_category(): void
    {
        $this->tour(['slug' => 'a-safari', 'name' => 'A Safari', 'category' => 'Wildlife']);
        $this->tour(['slug' => 'a-climb', 'name' => 'A Climb', 'category' => 'Mountain']);

        $this->get('/tours?category=wildlife')
            ->assertOk()
            ->assertSee('A Safari')
            ->assertDontSee('A Climb');
    }

    public function test_a_booking_enquiry_is_stored(): void
    {
        $this->tour(['slug' => 'bookable', 'name' => 'Bookable Trip']);

        $this->post('/inquiry', [
            'name' => 'Sam Booker',
            'email' => 'sam@example.com',
            'tour_slug' => 'bookable',
            'travellers' => 3,
        ])->assertRedirect(route('inquiry.create'));

        $this->assertDatabaseHas('inquiries', [
            'email' => 'sam@example.com',
            'tour_slug' => 'bookable',
            'tour_name' => 'Bookable Trip',
            'status' => 'new',
        ]);
    }
}
