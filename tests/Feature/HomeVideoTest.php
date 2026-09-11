<?php

namespace Tests\Feature;

use App\Filament\Resources\HomeVideos\Pages\EditHomeVideo;
use App\Models\HomeVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeVideoTest extends TestCase
{
    use RefreshDatabase;

    private const ID = 'pGx9BTRx3-w';

    private function video(array $overrides = []): HomeVideo
    {
        return HomeVideo::create(array_merge([
            'youtube_id' => self::ID,
            'eyebrow' => 'From our channel',
            'heading' => 'Take a glimpse into the safari',
            'video_title' => 'One day on the Serengeti',
            'caption' => 'Filmed by our guides',
            'is_published' => true,
        ], $overrides));
    }

    // --- The id, however it is pasted ------------------------------------

    /** @return array<string, array{0: string}> */
    public static function urlProvider(): array
    {
        return [
            'bare id' => [self::ID],
            'watch link' => ['https://www.youtube.com/watch?v=' . self::ID],
            'watch link, extra params' => ['https://www.youtube.com/watch?v=' . self::ID . '&t=42s'],
            'params before v' => ['https://www.youtube.com/watch?app=desktop&v=' . self::ID],
            'short share link' => ['https://youtu.be/' . self::ID],
            'short link with time' => ['https://youtu.be/' . self::ID . '?t=42'],
            'embed url' => ['https://www.youtube.com/embed/' . self::ID . '?rel=0'],
            'shorts url' => ['https://www.youtube.com/shorts/' . self::ID],
            'live url' => ['https://www.youtube.com/live/' . self::ID],
            'no scheme' => ['youtube.com/watch?v=' . self::ID],
            'surrounding whitespace' => ['  https://youtu.be/' . self::ID . '  '],
        ];
    }

    /**
     * @dataProvider urlProvider
     */
    public function test_the_id_is_pulled_out_of_whatever_was_pasted(string $input): void
    {
        // Asking staff to dig the id out of a share link is the commonest way
        // this field gets broken, so every shape YouTube hands out is accepted.
        $this->assertSame(self::ID, HomeVideo::extractId($input));
    }

    public function test_an_empty_value_becomes_null(): void
    {
        $this->assertNull(HomeVideo::extractId(''));
        $this->assertNull(HomeVideo::extractId('   '));
        $this->assertNull(HomeVideo::extractId(null));
    }

    public function test_the_id_is_normalised_on_save(): void
    {
        $video = $this->video(['youtube_id' => 'https://youtu.be/' . self::ID . '?t=10']);

        $this->assertSame(self::ID, $video->fresh()->youtube_id);
    }

    public function test_unrecognised_input_is_kept_rather_than_discarded(): void
    {
        // Better the admin sees their typo in the field than an empty box.
        $video = $this->video(['youtube_id' => 'not a video']);

        $this->assertSame('not a video', $video->fresh()->youtube_id);
        $this->assertFalse($video->fresh()->hasValidId());
    }

    // --- The section on the homepage -------------------------------------

    public function test_the_section_renders_with_the_embed(): void
    {
        $this->video();

        $this->get('/')
            ->assertOk()
            ->assertSee('From our channel')
            ->assertSee('Take a glimpse into the safari')
            ->assertSee('One day on the Serengeti')
            ->assertSee('Filmed by our guides')
            ->assertSee('https://www.youtube.com/embed/' . self::ID . '?rel=0', false);
    }

    public function test_the_iframe_is_lazy_and_titled(): void
    {
        // An eager YouTube iframe pulls several hundred KB before anyone has
        // decided to watch it.
        $this->video();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('title="One day on the Serengeti"', $html);
        $this->assertStringContainsString('allowfullscreen', $html);
    }

    public function test_an_unpublished_video_leaves_no_trace_on_the_homepage(): void
    {
        $this->video(['is_published' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Take a glimpse into the safari')
            ->assertDontSee('youtube.com/embed', false);
    }

    public function test_a_row_with_no_id_shows_no_empty_player(): void
    {
        // The seeded row starts empty; an empty black box mid-homepage is
        // worse than no section at all.
        $this->video(['youtube_id' => null]);

        $this->get('/')->assertOk()->assertDontSee('youtube.com/embed', false);
    }

    public function test_an_invalid_id_shows_no_player_either(): void
    {
        $this->video(['youtube_id' => 'oops']);

        $this->get('/')->assertOk()->assertDontSee('youtube.com/embed', false);
    }

    public function test_the_homepage_works_with_no_video_row_at_all(): void
    {
        $this->assertSame(0, HomeVideo::count());

        $this->get('/')->assertOk()->assertDontSee('youtube.com/embed', false);
    }

    public function test_the_caption_bar_is_skipped_when_both_fields_are_blank(): void
    {
        $this->video(['video_title' => null, 'caption' => null]);

        $this->get('/')
            ->assertOk()
            ->assertSee('youtube.com/embed', false)
            ->assertDontSee('yt-cap', false);
    }

    public function test_current_ignores_an_unpublished_row(): void
    {
        $this->video(['is_published' => false]);

        $this->assertNull(HomeVideo::current());
    }

    public function test_the_urls_are_built_from_the_id(): void
    {
        $video = $this->video();

        $this->assertSame('https://www.youtube.com/embed/' . self::ID . '?rel=0', $video->embedUrl());
        $this->assertSame('https://www.youtube.com/watch?v=' . self::ID, $video->watchUrl());
        $this->assertStringContainsString(self::ID, $video->thumbnailUrl());
    }

    // --- Admin ------------------------------------------------------------

    public function test_an_admin_can_set_the_video_by_id(): void
    {
        $video = $this->video(['youtube_id' => null, 'is_published' => false]);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditHomeVideo::class, ['record' => $video->getKey()])
            ->fillForm(['youtube_id' => self::ID, 'is_published' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(self::ID, $video->fresh()->youtube_id);

        $this->get('/')->assertOk()->assertSee('youtube.com/embed/' . self::ID, false);
    }

    public function test_an_admin_can_paste_a_whole_youtube_link(): void
    {
        $video = $this->video(['youtube_id' => null]);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditHomeVideo::class, ['record' => $video->getKey()])
            ->fillForm(['youtube_id' => 'https://www.youtube.com/watch?v=' . self::ID . '&t=30s'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(self::ID, $video->fresh()->youtube_id);
    }

    public function test_a_bad_id_is_refused_with_an_explanation(): void
    {
        $video = $this->video();

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditHomeVideo::class, ['record' => $video->getKey()])
            ->fillForm(['youtube_id' => 'https://vimeo.com/12345'])
            ->call('save')
            ->assertHasFormErrors(['youtube_id']);

        // The good value is still in the database.
        $this->assertSame(self::ID, $video->fresh()->youtube_id);
    }

    public function test_an_admin_can_change_the_wording(): void
    {
        $video = $this->video();

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditHomeVideo::class, ['record' => $video->getKey()])
            ->fillForm([
                'eyebrow' => 'Watch this',
                'heading' => 'Our latest film',
                'video_title' => 'Ngorongoro at dawn',
                'caption' => 'Shot on location',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('Watch this')
            ->assertSee('Our latest film')
            ->assertSee('Ngorongoro at dawn')
            ->assertSee('Shot on location');
    }

    public function test_an_admin_can_hide_the_section(): void
    {
        $video = $this->video();

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(EditHomeVideo::class, ['record' => $video->getKey()])
            ->fillForm(['is_published' => false])
            ->call('save');

        $this->get('/')->assertOk()->assertDontSee('youtube.com/embed', false);
    }

    public function test_the_editor_needs_an_admin(): void
    {
        $video = $this->video();
        $url = "/admin/home-videos/{$video->id}/edit";

        $this->get($url)->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get($url)
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get($url)
            ->assertOk();
    }

    public function test_the_video_cannot_be_created_or_deleted_from_the_panel(): void
    {
        // One row, so the sidebar link opens the editor rather than a list.
        $resource = \App\Filament\Resources\HomeVideos\HomeVideoResource::class;

        $this->assertFalse($resource::canCreate());
        $this->assertFalse($resource::canDelete($this->video()));
    }

    public function test_it_appears_in_the_admin_sidebar(): void
    {
        /*
         * Filament's own getNavigationItems() returns nothing for a resource
         * with no index page, so this one builds its item by hand. Without
         * that override the resource is routable but invisible — which is
         * exactly how it shipped the first time.
         */
        $resource = \App\Filament\Resources\HomeVideos\HomeVideoResource::class;

        $items = $resource::getNavigationItems();

        $this->assertCount(1, $items, 'no sidebar item for the homepage video');
        $this->assertSame('Homepage Video', $items[0]->getLabel());
        $this->assertSame('Site', $items[0]->getGroup());
        $this->assertStringContainsString('/admin/home-videos/', $items[0]->getUrl());
    }

    public function test_the_sidebar_link_is_rendered_on_an_admin_page(): void
    {
        $this->video();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin')
            ->assertOk()
            ->assertSee('Homepage Video');
    }

}
