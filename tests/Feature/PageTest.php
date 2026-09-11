<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    private function page(string $slug, array $overrides = []): Page
    {
        return Page::create(array_merge([
            'slug' => $slug,
            'title' => $slug === Page::TEAM ? 'Our Team' : 'About Us',
            'eyebrow' => 'Who we are',
            'heading' => 'A Tanzanian operator',
            'intro' => 'Based in Arusha.',
            'sections' => [],
            'is_published' => true,
        ], $overrides));
    }

    private function member(array $overrides = []): TeamMember
    {
        return TeamMember::create(array_merge([
            'name' => 'Amani Mushi',
            'role' => 'Head Guide',
            'bio' => 'Fifteen years in the northern circuit.',
            'is_published' => true,
        ], $overrides));
    }

    public function test_the_about_page_shows_its_content(): void
    {
        $this->page(Page::ABOUT);

        $this->get('/about')
            ->assertOk()
            ->assertSee('A Tanzanian operator')
            ->assertSee('Who we are')
            ->assertSee('Based in Arusha.');
    }

    public function test_the_about_page_renders_its_sections(): void
    {
        $this->page(Page::ABOUT, [
            'sections' => [
                ['heading' => 'How we work', 'body' => 'We plan it ourselves.', 'image' => null],
                ['heading' => 'Our guides', 'body' => 'Licensed and local.', 'image' => null],
            ],
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('How we work')
            ->assertSee('We plan it ourselves.')
            ->assertSee('Our guides');
    }

    public function test_a_blank_section_row_is_not_rendered(): void
    {
        // An empty repeater row must not leave a gap on the page.
        $page = $this->page(Page::ABOUT, [
            'sections' => [
                ['heading' => 'Real section', 'body' => 'Real copy.', 'image' => null],
                ['heading' => null, 'body' => null, 'image' => null],
            ],
        ]);

        $this->assertCount(1, $page->body_sections);
    }

    public function test_a_double_newline_becomes_separate_paragraphs(): void
    {
        $this->page(Page::ABOUT, [
            'sections' => [
                ['heading' => 'Story', 'body' => "First para.\n\nSecond para.", 'image' => null],
            ],
        ]);

        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('<p>First para.</p>', $html);
        $this->assertStringContainsString('<p>Second para.</p>', $html);
    }

    public function test_an_unpublished_page_is_not_reachable(): void
    {
        $this->page(Page::ABOUT, ['is_published' => false]);

        $this->get('/about')->assertNotFound();
    }

    public function test_a_missing_page_record_404s_rather_than_erroring(): void
    {
        $this->get('/about')->assertNotFound();
    }

    public function test_the_team_page_lists_published_members(): void
    {
        $this->page(Page::TEAM);
        $this->member(['name' => 'Amani Mushi']);
        $this->member(['name' => 'Neema Kileo', 'role' => 'Trip Planner']);

        $this->get('/about/team')
            ->assertOk()
            ->assertSee('Amani Mushi')
            ->assertSee('Neema Kileo')
            ->assertSee('Trip Planner');
    }

    public function test_a_hidden_member_is_left_off_the_page(): void
    {
        $this->page(Page::TEAM);
        $this->member(['name' => 'Shown Person']);
        $this->member(['name' => 'Hidden Person', 'is_published' => false]);

        $this->get('/about/team')
            ->assertOk()
            ->assertSee('Shown Person')
            ->assertDontSee('Hidden Person');
    }

    public function test_members_appear_in_the_admin_order(): void
    {
        $this->page(Page::TEAM);
        $this->member(['name' => 'Third', 'sort_order' => 3]);
        $this->member(['name' => 'First', 'sort_order' => 1]);
        $this->member(['name' => 'Second', 'sort_order' => 2]);

        $html = $this->get('/about/team')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'Second'), strpos($html, 'First'));
        $this->assertLessThan(strpos($html, 'Third'), strpos($html, 'Second'));
    }

    public function test_the_team_page_works_before_anyone_is_added(): void
    {
        // The page must read sensibly on a fresh install, not show an empty grid.
        $this->page(Page::TEAM, ['heading' => 'Meet the team']);

        $this->get('/about/team')
            ->assertOk()
            ->assertSee('Meet the team')
            // The closing prompt still renders; only the member grid is skipped.
            ->assertSee('Travel with us');
    }

    public function test_a_member_without_a_photo_falls_back_to_initials(): void
    {
        $this->page(Page::TEAM);
        $this->member(['name' => 'Amani Mushi', 'photo' => null]);

        $this->get('/about/team')->assertOk()->assertSee('AM');
    }

    public function test_contact_details_are_only_shown_when_filled_in(): void
    {
        $this->page(Page::TEAM);
        $this->member(['name' => 'Private Person']);
        $this->member(['name' => 'Public Person', 'email' => 'public@example.com']);

        $this->get('/about/team')
            ->assertOk()
            ->assertSee('public@example.com');
    }

    public function test_the_meta_title_falls_back_to_the_page_title(): void
    {
        $page = $this->page(Page::ABOUT, ['meta_title' => null]);

        $this->assertSame('About Us', $page->metaTitle());
        $this->assertSame('Custom', $this->page('other', ['meta_title' => 'Custom'])->metaTitle());
    }

    public function test_the_meta_description_falls_back_to_the_intro(): void
    {
        $page = $this->page(Page::ABOUT, [
            'meta_description' => null,
            'intro' => "Based   in\n Arusha.",
        ]);

        // Squished, so stray whitespace from a textarea never reaches Google.
        $this->assertSame('Based in Arusha.', $page->metaDescription());
    }

    public function test_the_pages_are_managed_in_the_admin_panel(): void
    {
        $page = $this->page(Page::ABOUT);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/pages')->assertOk();
        $this->actingAs($admin)->get("/admin/pages/{$page->id}/edit")->assertOk();
    }

    public function test_an_admin_can_rewrite_the_about_page(): void
    {
        $page = $this->page(Page::ABOUT);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm([
                'title' => 'About TWINS AFRICAN',
                'eyebrow' => 'Our story',
                'heading' => 'Twenty years on these roads',
                'intro' => 'We started with one vehicle.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $page->refresh();

        $this->assertSame('Twenty years on these roads', $page->heading);
        $this->assertSame('We started with one vehicle.', $page->intro);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Twenty years on these roads')
            ->assertSee('We started with one vehicle.');
    }

    public function test_an_admin_can_add_a_section_to_the_about_page(): void
    {
        $page = $this->page(Page::ABOUT, ['sections' => []]);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm([
                'sections' => [
                    ['heading' => 'Why us', 'body' => 'Because we live here.', 'image' => null],
                    ['heading' => 'Our vehicles', 'body' => 'Serviced weekly.', 'image' => null],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertCount(2, $page->refresh()->sections);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Why us')
            ->assertSee('Because we live here.')
            ->assertSee('Our vehicles');
    }

    public function test_an_admin_can_remove_a_section_again(): void
    {
        $page = $this->page(Page::ABOUT, [
            'sections' => [
                ['heading' => 'Keep me', 'body' => 'Still here.', 'image' => null],
                ['heading' => 'Delete me', 'body' => 'Gone soon.', 'image' => null],
            ],
        ]);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm([
                'sections' => [
                    ['heading' => 'Keep me', 'body' => 'Still here.', 'image' => null],
                ],
            ])
            ->call('save');

        $this->assertCount(1, $page->refresh()->sections);

        $this->get('/about')->assertOk()->assertDontSee('Delete me');
    }

    public function test_an_admin_can_take_the_about_page_offline(): void
    {
        $page = $this->page(Page::ABOUT);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['is_published' => false])
            ->call('save');

        $this->get('/about')->assertNotFound();
    }

    public function test_the_team_page_intro_is_editable_too(): void
    {
        $page = $this->page(Page::TEAM);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditPage::class, ['record' => $page->getKey()])
            ->fillForm(['intro' => 'Twelve guides and four planners.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/about/team')->assertOk()->assertSee('Twelve guides and four planners.');
    }

    public function test_pages_cannot_be_created_or_deleted_from_the_panel(): void
    {
        // Every page's slug has to match a declared route, so inventing one
        // in the panel would produce a record with nowhere to live.
        $this->assertFalse(\App\Filament\Resources\Pages\PageResource::canCreate());
        $this->assertFalse(
            \App\Filament\Resources\Pages\PageResource::canDelete($this->page(Page::ABOUT))
        );
    }

    public function test_the_team_resource_is_reachable(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/team-members')->assertOk();
        $this->actingAs($admin)->get('/admin/team-members/create')->assertOk();
    }

    public function test_the_admin_pages_need_an_admin(): void
    {
        $this->get('/admin/pages')->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/pages')
            ->assertForbidden();
    }

    public function test_the_nav_links_now_resolve(): void
    {
        // /about and /about/team were linked in the navigation but 404ing.
        $this->page(Page::ABOUT);
        $this->page(Page::TEAM);

        $this->get('/about')->assertOk();
        $this->get('/about/team')->assertOk();
    }
}
