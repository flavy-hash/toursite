<?php

namespace Tests\Feature;

use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryVideoTest extends TestCase
{
    use RefreshDatabase;

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
            'summary' => ['A paragraph.'],
            'highlights' => ['A highlight'],
            'itinerary' => [],
            'included' => [],
            'excluded' => [],
            'gallery' => [],
            'is_published' => true,
        ], $overrides));
    }

    public function test_a_video_appears_in_the_gallery(): void
    {
        $this->tour(['gallery_videos' => ['tours/gallery/video/drive.mp4']]);

        $this->get('/tours/test-safari')
            ->assertOk()
            ->assertSee('Gallery')
            ->assertSee('/storage/tours/gallery/video/drive.mp4', false);
    }

    public function test_videos_come_before_photos(): void
    {
        // A clip is the thing worth watching, so it leads the grid.
        $tour = $this->tour([
            'gallery' => ['tours/gallery/one.jpg'],
            'gallery_videos' => ['tours/gallery/video/drive.mp4'],
        ]);

        $types = array_column($tour->gallery_items, 'type');

        $this->assertSame(['video', 'image'], $types);
    }

    public function test_a_video_tile_is_marked_for_the_viewer(): void
    {
        $this->tour(['gallery_videos' => ['tours/gallery/video/drive.mp4']]);

        $this->get('/tours/test-safari')
            ->assertOk()
            ->assertSee('data-lbx-type="video"', false)
            ->assertSee('data-lbx-mime="video/mp4"', false)
            // Still a real link, so the clip opens even without JavaScript.
            ->assertSee('data-lbx="tour-gallery"', false);
    }

    public function test_the_mime_type_is_worked_out_from_the_extension(): void
    {
        $this->assertSame('video/mp4', Tour::videoMime('/storage/a/clip.mp4'));
        $this->assertSame('video/webm', Tour::videoMime('/storage/a/clip.webm'));
        $this->assertSame('video/quicktime', Tour::videoMime('/storage/a/clip.MOV'));
        $this->assertNull(Tour::videoMime('/storage/a/clip.avi'));
    }

    public function test_a_query_string_does_not_confuse_the_mime_lookup(): void
    {
        $this->assertSame('video/mp4', Tour::videoMime('https://example.com/clip.mp4?v=2'));
    }

    public function test_a_gallery_of_photos_alone_still_works(): void
    {
        // The existing behaviour must be untouched for packages with no video.
        $tour = $this->tour(['gallery' => ['tours/gallery/one.jpg', 'tours/gallery/two.jpg']]);

        $this->assertSame(['image', 'image'], array_column($tour->gallery_items, 'type'));

        $this->get('/tours/test-safari')
            ->assertOk()
            ->assertSee('Gallery')
            ->assertDontSee('data-lbx-type="video"', false);
    }

    public function test_a_package_with_neither_shows_no_gallery(): void
    {
        $this->tour(['gallery' => [], 'gallery_videos' => []]);

        $this->get('/tours/test-safari')->assertOk()->assertDontSee('Gallery');
    }

    public function test_video_urls_resolve_the_same_way_photos_do(): void
    {
        // Root-relative, so they work whatever host is serving the request.
        $tour = $this->tour(['gallery_videos' => ['tours/gallery/video/drive.mp4']]);

        $this->assertSame(['/storage/tours/gallery/video/drive.mp4'], $tour->gallery_video_urls);
    }

    public function test_an_external_video_url_is_left_alone(): void
    {
        $tour = $this->tour(['gallery_videos' => ['https://cdn.example.com/clip.mp4']]);

        $this->assertSame(['https://cdn.example.com/clip.mp4'], $tour->gallery_video_urls);
    }

    public function test_livewire_allows_a_video_sized_upload(): void
    {
        /*
         * Livewire checks its own rule before any Filament field's maxSize(),
         * and its default is 12 MB - which silently rejected every package
         * video. If this ever drops back below the field's limit, uploads
         * fail again with no useful message.
         */
        $rules = config('livewire.temporary_file_upload.rules');

        $this->assertIsArray($rules, 'livewire config is not published');

        $max = collect($rules)
            ->map(fn ($r) => is_string($r) && str_starts_with($r, 'max:') ? (int) substr($r, 4) : null)
            ->filter()
            ->first();

        $this->assertNotNull($max, 'no max: rule on livewire temporary uploads');
        $this->assertGreaterThanOrEqual(51200, $max, 'livewire caps uploads below 50 MB');
    }

    public function test_the_video_field_does_not_promise_more_than_livewire_allows(): void
    {
        $rules = config('livewire.temporary_file_upload.rules');
        $livewireMax = collect($rules)
            ->map(fn ($r) => is_string($r) && str_starts_with($r, 'max:') ? (int) substr($r, 4) : null)
            ->filter()
            ->first();

        // The form reads the same env value, so the two cannot drift apart.
        $fieldMax = (int) env('LIVEWIRE_UPLOAD_MAX_KB', 102400);

        $this->assertLessThanOrEqual(
            $livewireMax,
            $fieldMax,
            'the upload field allows more than Livewire will accept'
        );
    }

    public function test_the_admin_form_offers_a_video_upload(): void
    {
        $admin = \App\Models\User::factory()->create(['is_admin' => true]);
        $tour = $this->tour();

        $this->actingAs($admin)
            ->get("/admin/tours/{$tour->id}/edit")
            ->assertOk()
            ->assertSee('Videos');
    }
}
