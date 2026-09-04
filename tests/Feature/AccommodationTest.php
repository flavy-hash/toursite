<?php

namespace Tests\Feature;

use App\Filament\Resources\Accommodations\Pages\CreateAccommodation;
use App\Filament\Resources\Accommodations\Pages\EditAccommodation;
use App\Filament\Resources\Accommodations\Pages\ListAccommodations;
use App\Models\Accommodation;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccommodationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function property(array $overrides = []): Accommodation
    {
        return Accommodation::create(array_merge([
            'slug' => 'serengeti-river-lodge',
            'name' => 'Serengeti River Lodge',
            'type' => 'Lodge',
            'level' => 'luxury',
            'location' => 'Central Serengeti',
            'region' => 'northern',
            'rating' => 5,
            'description' => 'A sanctuary of comfort deep in the savannah.',
            'price_impact' => '+$350 per person per night',
            'board_basis' => 'Full board',
            'image' => 'accommodations/lodge.jpg',
            'amenities' => ['Pool', 'Wi-Fi', 'Game-viewing deck'],
        ], $overrides));
    }

    private function tour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'slug' => 'migration',
            'name' => 'Great Migration Safari',
            'category' => 'Wildlife',
            'difficulty' => 'Easy',
            'image' => 'tours/hero.jpg',
            'days' => '7 Days',
            'price' => '$2,450',
            'rating' => 4.9,
            'reviews' => 12,
        ], $overrides));
    }

    public function test_the_admin_can_manage_accommodation(): void
    {
        $property = $this->property();

        $this->actingAs($this->admin())->get('/admin/accommodations')->assertOk()->assertSee('Serengeti River Lodge');
        $this->actingAs($this->admin())->get('/admin/accommodations/create')->assertOk();
        $this->actingAs($this->admin())->get("/admin/accommodations/{$property->id}/edit")->assertOk();
    }

    public function test_a_property_can_be_attached_to_a_package(): void
    {
        $tour = $this->tour();
        $property = $this->property();

        $tour->accommodations()->attach($property, ['nights' => 3, 'sort_order' => 1]);

        $this->assertSame(1, $tour->accommodations()->count());
        $this->assertSame(3, (int) $tour->accommodations()->first()->pivot->nights);
    }

    public function test_the_same_property_cannot_be_attached_twice(): void
    {
        $tour = $this->tour();
        $property = $this->property();

        $tour->accommodations()->attach($property);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $tour->accommodations()->attach($property);
    }

    public function test_a_property_is_shared_across_packages(): void
    {
        // Editing a lodge once should update every trip that uses it.
        $property = $this->property();
        $one = $this->tour(['slug' => 'one', 'name' => 'One']);
        $two = $this->tour(['slug' => 'two', 'name' => 'Two']);

        $one->accommodations()->attach($property);
        $two->accommodations()->attach($property);

        $property->update(['name' => 'Renamed Lodge']);

        $this->assertSame('Renamed Lodge', $one->accommodations()->first()->name);
        $this->assertSame('Renamed Lodge', $two->accommodations()->first()->name);
    }

    public function test_accommodation_appears_on_the_tour_page(): void
    {
        $tour = $this->tour();
        $tour->accommodations()->attach($this->property(), ['nights' => 3, 'sort_order' => 0]);

        $this->get('/tours/migration')
            ->assertOk()
            ->assertSee('Where you&rsquo;ll stay', false)
            ->assertSee('Serengeti River Lodge')
            ->assertSee('3 nights')
            ->assertSee('Full board')
            ->assertSee('+$350 per person per night');
    }

    public function test_unpublished_properties_are_hidden_from_the_tour_page(): void
    {
        $tour = $this->tour();
        $tour->accommodations()->attach($this->property(['is_published' => false]));

        $this->get('/tours/migration')
            ->assertOk()
            ->assertDontSee('Serengeti River Lodge')
            ->assertDontSee('Where you&rsquo;ll stay', false);
    }

    public function test_the_section_is_skipped_when_nothing_is_attached(): void
    {
        $this->tour();

        $this->get('/tours/migration')->assertOk()->assertDontSee('Where you&rsquo;ll stay', false);
    }

    public function test_properties_render_in_their_running_order(): void
    {
        $tour = $this->tour();
        $tour->accommodations()->attach($this->property(['slug' => 'second', 'name' => 'Second Camp']), ['sort_order' => 2]);
        $tour->accommodations()->attach($this->property(['slug' => 'first', 'name' => 'First Camp']), ['sort_order' => 1]);

        $html = $this->get('/tours/migration')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'Second Camp'), strpos($html, 'First Camp'));
    }

    public function test_detaching_a_package_does_not_delete_the_property(): void
    {
        $tour = $this->tour();
        $property = $this->property();
        $tour->accommodations()->attach($property);

        $tour->accommodations()->detach($property);

        $this->assertDatabaseHas('accommodations', ['id' => $property->id]);
        $this->assertSame(0, $tour->accommodations()->count());
    }

    public function test_deleting_a_package_clears_its_attachments(): void
    {
        $tour = $this->tour();
        $property = $this->property();
        $tour->accommodations()->attach($property);

        $tour->delete();

        $this->assertDatabaseMissing('accommodation_tour', ['tour_id' => $tour->id]);
        $this->assertDatabaseHas('accommodations', ['id' => $property->id]);
    }

    public function test_the_listing_counts_how_many_packages_use_a_property(): void
    {
        $property = $this->property();
        $this->tour(['slug' => 'a', 'name' => 'A'])->accommodations()->attach($property);
        $this->tour(['slug' => 'b', 'name' => 'B'])->accommodations()->attach($property);

        Livewire::actingAs($this->admin())
            ->test(ListAccommodations::class)
            ->assertOk()
            ->assertSee('2 packages');
    }

    public function test_packages_can_be_chosen_while_creating_a_property(): void
    {
        $one = $this->tour(['slug' => 'one', 'name' => 'Migration Safari']);
        $two = $this->tour(['slug' => 'two', 'name' => 'Crater Descent']);

        Livewire::actingAs($this->admin())
            ->test(CreateAccommodation::class)
            ->fillForm([
                'name' => 'Kubu Kubu Tented Lodge',
                'slug' => 'kubu-kubu',
                'type' => 'Tented Camp',
                'level' => 'classic',
                'tours' => [$one->id, $two->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $property = Accommodation::firstWhere('slug', 'kubu-kubu');

        $this->assertNotNull($property);
        $this->assertEqualsCanonicalizing(
            ['Migration Safari', 'Crater Descent'],
            $property->tours->pluck('name')->all()
        );
    }

    public function test_editing_a_property_syncs_its_packages(): void
    {
        $one = $this->tour(['slug' => 'one', 'name' => 'Migration Safari']);
        $two = $this->tour(['slug' => 'two', 'name' => 'Crater Descent']);

        $property = $this->property();
        $property->tours()->attach([$one->id, $two->id]);

        // Deselecting must detach, not just fail to add.
        Livewire::actingAs($this->admin())
            ->test(EditAccommodation::class, ['record' => $property->getKey()])
            ->fillForm(['tours' => [$two->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Crater Descent'], $property->fresh()->tours->pluck('name')->all());
    }

    public function test_a_property_chosen_this_way_shows_on_the_package_page(): void
    {
        $tour = $this->tour();

        Livewire::actingAs($this->admin())
            ->test(CreateAccommodation::class)
            ->fillForm([
                'name' => 'Kubu Kubu Tented Lodge',
                'slug' => 'kubu-kubu',
                'type' => 'Tented Camp',
                'level' => 'classic',
                'tours' => [$tour->id],
            ])
            ->call('create');

        $this->get('/tours/migration')
            ->assertOk()
            ->assertSee('Kubu Kubu Tented Lodge');
    }

    public function test_the_gallery_is_shown_on_the_package_page(): void
    {
        $tour = $this->tour();
        $tour->accommodations()->attach($this->property([
            'gallery' => [
                'accommodations/gallery/one.jpg',
                'accommodations/gallery/two.jpg',
            ],
        ]));

        $this->get('/tours/migration')
            ->assertOk()
            ->assertSee('/storage/accommodations/gallery/one.jpg', false)
            ->assertSee('/storage/accommodations/gallery/two.jpg', false)
            ->assertSee('3 photos');
    }

    public function test_every_photo_of_one_property_shares_a_viewer_group(): void
    {
        // The arrows should walk that property, not the whole page.
        $tour = $this->tour();
        $property = $this->property(['gallery' => ['accommodations/gallery/one.jpg']]);
        $tour->accommodations()->attach($property);

        $html = $this->get('/tours/migration')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'data-lbx="stay-' . $property->id . '"'));
    }

    public function test_two_properties_get_separate_viewer_groups(): void
    {
        $tour = $this->tour();
        $one = $this->property(['slug' => 'one', 'name' => 'One Lodge']);
        $two = $this->property(['slug' => 'two', 'name' => 'Two Camp']);
        $tour->accommodations()->attach([$one->id, $two->id]);

        $html = $this->get('/tours/migration')->assertOk()->getContent();

        $this->assertStringContainsString('data-lbx="stay-' . $one->id . '"', $html);
        $this->assertStringContainsString('data-lbx="stay-' . $two->id . '"', $html);
    }

    public function test_photos_stay_reachable_without_javascript(): void
    {
        // Each trigger is a real link to the full image; the viewer only
        // enhances it.
        $tour = $this->tour();
        $tour->accommodations()->attach($this->property());

        $this->get('/tours/migration')
            ->assertOk()
            ->assertSee('href="/storage/accommodations/lodge.jpg"', false);
    }

    public function test_a_property_with_no_photo_shows_a_placeholder(): void
    {
        $tour = $this->tour();
        $tour->accommodations()->attach($this->property(['image' => null, 'gallery' => null]));

        $this->get('/tours/migration')->assertOk()->assertSee('Photo to come');
    }

    public function test_the_viewer_is_present_on_the_page(): void
    {
        $this->get('/')->assertOk()->assertSee('id="lbx"', false);
    }

    public function test_a_property_with_no_gallery_renders_fine(): void
    {
        $tour = $this->tour();
        $tour->accommodations()->attach($this->property(['gallery' => null]));

        $this->get('/tours/migration')->assertOk()->assertSee('Serengeti River Lodge');
    }

    public function test_a_slug_typed_with_spaces_is_normalised(): void
    {
        // A slug with spaces or capitals cannot match the route, so the
        // package page would 404 while looking saved.
        Livewire::actingAs($this->admin())
            ->test(CreateAccommodation::class)
            ->fillForm([
                'name' => 'Winsome Hotel',
                'slug' => 'Where The Sun Rise',
                'type' => 'Hotel',
                'level' => 'classic',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('accommodations', ['slug' => 'where-the-sun-rise']);
    }

    public function test_image_urls_are_root_relative(): void
    {
        $property = $this->property(['gallery' => ['accommodations/gallery/a.jpg']]);

        $this->assertSame('/storage/accommodations/lodge.jpg', $property->image_url);
        $this->assertSame(['/storage/accommodations/gallery/a.jpg'], $property->gallery_urls);
    }
}
