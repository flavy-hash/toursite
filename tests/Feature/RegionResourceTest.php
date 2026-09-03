<?php

namespace Tests\Feature;

use App\Filament\Resources\RegionTours\Kilimanjaro\KilimanjaroTourResource;
use App\Filament\Resources\RegionTours\SouthernCircuit\SouthernCircuitTourResource;
use App\Filament\Resources\RegionTours\Zanzibar\ZanzibarTourResource;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function tour(string $slug, string $name, ?string $region, string $category = 'Wildlife'): Tour
    {
        return Tour::create([
            'slug' => $slug,
            'name' => $name,
            'region' => $region,
            'category' => $category,
            'difficulty' => 'Easy',
            'image' => 'tours/x.jpg',
            'days' => '5 Days',
            'price' => '$1,000',
            'rating' => 4.5,
            'reviews' => 1,
        ]);
    }

    /** @return array<int, array{0: class-string, 1: string, 2: string}> */
    public static function regions(): array
    {
        return [
            'kilimanjaro' => [KilimanjaroTourResource::class, 'kilimanjaro', 'kilimanjaro-tours'],
            'zanzibar' => [ZanzibarTourResource::class, 'zanzibar', 'zanzibar-tours'],
            'southern' => [SouthernCircuitTourResource::class, 'southern', 'southern-circuit-tours'],
        ];
    }

    /**
     * @dataProvider regions
     */
    public function test_each_section_lists_only_its_own_region(string $resource, string $region, string $slug): void
    {
        $this->tour("mine-{$region}", 'Belongs Here', $region);
        $this->tour('elsewhere', 'Somewhere Else', 'northern');

        $this->actingAs($this->admin())
            ->get("/admin/{$slug}")
            ->assertOk()
            ->assertSee('Belongs Here')
            ->assertDontSee('Somewhere Else');
    }

    /**
     * @dataProvider regions
     */
    public function test_each_section_opens_its_create_screen(string $resource, string $region, string $slug): void
    {
        $this->actingAs($this->admin())
            ->get("/admin/{$slug}/create")
            ->assertOk();
    }

    /**
     * @dataProvider regions
     */
    public function test_the_query_is_scoped_to_the_region(string $resource, string $region): void
    {
        $this->tour("mine-{$region}", 'Belongs Here', $region);
        $this->tour('elsewhere', 'Somewhere Else', 'northern');

        $names = $resource::getEloquentQuery()->pluck('name')->all();

        $this->assertSame(['Belongs Here'], $names);
        $this->assertSame($region, $resource::region());
    }

    /**
     * @dataProvider regions
     */
    public function test_a_package_created_in_a_section_is_stamped_with_its_region(string $resource, string $region, string $slug): void
    {
        // The region field is disabled in the form, so the create page has to
        // supply it — otherwise new packages would land nowhere.
        $page = new \ReflectionClass($resource::getPages()['create']->getPage());
        $method = $page->getMethod('mutateFormDataBeforeCreate');
        $method->setAccessible(true);

        $instance = $page->newInstanceWithoutConstructor();
        $data = $method->invoke($instance, ['name' => 'New Trip']);

        $this->assertSame($region, $data['region']);
    }

    public function test_a_region_package_reaches_its_public_nav_link(): void
    {
        $this->tour('kili-new', 'Lemosho Route', 'kilimanjaro', 'Mountain');

        $this->get('/tours?region=kilimanjaro')
            ->assertOk()
            ->assertSee('Lemosho Route');
    }

    public function test_the_master_tour_list_still_shows_every_region(): void
    {
        $this->tour('a', 'Kili Trip', 'kilimanjaro');
        $this->tour('b', 'Zanzibar Trip', 'zanzibar');
        $this->tour('c', 'South Trip', 'southern');

        $this->actingAs($this->admin())
            ->get('/admin/tours')
            ->assertOk()
            ->assertSee('Kili Trip')
            ->assertSee('Zanzibar Trip')
            ->assertSee('South Trip');
    }
}
