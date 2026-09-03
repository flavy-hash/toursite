<?php

namespace Tests\Feature;

use App\Models\NavItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function headerItem(array $overrides = []): NavItem
    {
        return NavItem::create(array_merge([
            'location' => NavItem::HEADER,
            'label' => 'Kilimanjaro',
            'path' => '/tours?region=kilimanjaro',
            'sort_order' => 0,
            'panel_heading' => 'Climbing the Roof of Africa',
            'panel_copy' => 'Rainforest to glacier.',
            'panel_cta_label' => 'Climb Kilimanjaro',
            'panel_cta_path' => '/tours?region=kilimanjaro',
            'rail' => [['name' => 'Machame', 'path' => '/tours/kilimanjaro-machame']],
        ], $overrides));
    }

    public function test_the_admin_can_manage_navigation(): void
    {
        $item = $this->headerItem();

        $this->actingAs($this->admin())->get('/admin/nav-items')->assertOk()->assertSee('Kilimanjaro');
        $this->actingAs($this->admin())->get('/admin/nav-items/create')->assertOk();
        $this->actingAs($this->admin())->get("/admin/nav-items/{$item->id}/edit")->assertOk();
    }

    public function test_a_header_item_renders_in_the_site_navigation(): void
    {
        $this->headerItem(['label' => 'Southern Circuit']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Southern Circuit')
            ->assertSee('Climbing the Roof of Africa')
            ->assertSee('Machame');
    }

    public function test_editing_a_label_changes_the_site_navigation(): void
    {
        $item = $this->headerItem();

        $this->get('/')->assertSee('Kilimanjaro');

        $item->update(['label' => 'Mountain Climbs']);

        $this->get('/')->assertSee('Mountain Climbs')->assertDontSee('>Kilimanjaro<', escape: false);
    }

    public function test_hiding_an_item_removes_it_from_the_site(): void
    {
        $item = $this->headerItem(['label' => 'Temporary Link']);

        $this->get('/')->assertSee('Temporary Link');

        $item->update(['is_active' => false]);

        $this->get('/')->assertDontSee('Temporary Link');
    }

    public function test_sort_order_controls_the_order_on_the_site(): void
    {
        $this->headerItem(['label' => 'Second', 'sort_order' => 2, 'panel_heading' => null, 'rail' => null]);
        $this->headerItem(['label' => 'First', 'sort_order' => 1, 'panel_heading' => null, 'rail' => null]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'Second'),
            strpos($html, 'First'),
            'Items should render in sort_order.'
        );
    }

    public function test_an_item_without_a_panel_renders_as_a_plain_link(): void
    {
        $this->headerItem([
            'label' => 'Contact',
            'path' => '/contact',
            'panel_heading' => null,
            'panel_copy' => null,
            'rail' => null,
        ]);

        $item = NavItem::where('label', 'Contact')->first();

        $this->assertFalse($item->hasPanel());
        $this->get('/')->assertOk()->assertSee('Contact');
    }

    public function test_the_panel_paragraph_can_be_edited(): void
    {
        $item = $this->headerItem(['panel_copy' => 'The original wording.']);

        $this->get('/')->assertSee('The original wording.');

        $item->update(['panel_copy' => 'Completely different wording.']);

        $this->get('/')
            ->assertSee('Completely different wording.')
            ->assertDontSee('The original wording.');
    }

    public function test_the_rail_links_can_be_edited(): void
    {
        $item = $this->headerItem([
            'rail' => [['name' => 'Budget', 'path' => '/tours?tier=budget']],
        ]);

        $this->get('/')->assertSee('Budget');

        $item->update([
            'rail' => [
                ['name' => 'Value', 'path' => '/tours?tier=budget'],
                ['name' => 'Premium', 'path' => '/tours?tier=luxury'],
            ],
        ]);

        $this->get('/')
            ->assertSee('Value')
            ->assertSee('Premium')
            ->assertDontSee('Budget');
    }

    public function test_the_panel_image_is_stored_as_a_disk_key_so_it_can_be_replaced(): void
    {
        $item = $this->headerItem(['panel_image' => 'nav/panel.jpg']);

        // Filament can only preview and replace files it finds on the upload
        // disk, so a /public path would make the field uneditable.
        $this->assertStringStartsNotWith('/', $item->panel_image);
        $this->assertSame('/storage/nav/panel.jpg', $item->panel_image_url);

        $this->get('/')->assertSee('src="/storage/nav/panel.jpg"', escape: false);
    }

    public function test_a_bottom_bar_item_renders_with_its_icon(): void
    {
        NavItem::create([
            'location' => NavItem::BOTTOM,
            'label' => 'Climbing',
            'path' => '/tours?region=kilimanjaro',
            'icon' => 'mountain',
            'sort_order' => 0,
        ]);

        $this->get('/')->assertOk()->assertSee('Climbing');
    }

    public function test_contact_links_scroll_to_the_footer(): void
    {
        NavItem::create([
            'location' => NavItem::HEADER,
            'label' => 'Contact',
            'path' => '#contact',
            'sort_order' => 9,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        // The anchor has to exist or the link goes nowhere.
        $this->assertStringContainsString('href="#contact"', $html);
        $this->assertStringContainsString('<footer id="contact"', $html);
    }

    public function test_the_footer_anchor_is_present_on_every_page(): void
    {
        // Contact points at the footer, so an interior page must carry it too.
        foreach (['/', '/tours', '/reviews'] as $path) {
            $this->get($path)->assertOk()->assertSee('<footer id="contact"', false);
        }
    }

    public function test_the_whatsapp_button_uses_the_configured_number(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('https://wa.me/' . config('site.contact.whatsapp'), false);
    }

    public function test_the_site_still_renders_with_no_navigation_at_all(): void
    {
        // Deleting every item must not take the homepage down with it.
        NavItem::query()->delete();

        $this->get('/')->assertOk();
    }

    public function test_rail_links_tolerate_incomplete_rows(): void
    {
        $item = $this->headerItem([
            'rail' => [
                ['name' => 'Good', 'path' => '/ok'],
                ['name' => '', 'path' => '/blank'],
                ['name' => 'No path'],
            ],
        ]);

        $this->assertSame([
            ['name' => 'Good', 'path' => '/ok'],
            ['name' => 'No path', 'path' => '#'],
        ], $item->railLinks());
    }
}
