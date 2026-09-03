<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Support\DocumentParagraphs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class TourMediaTest extends TestCase
{
    use RefreshDatabase;

    private function tour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'slug' => 'media-trip',
            'name' => 'Media Trip',
            'tagline' => 'A tagline',
            'category' => 'Wildlife',
            'difficulty' => 'Easy',
            'image' => 'tours/uploaded.jpg',
            'days' => '5 Days',
            'price' => '$1,000',
            'rating' => 4.8,
            'reviews' => 10,
        ], $overrides));
    }

    /**
     * Builds a minimal but structurally valid .docx.
     */
    private function makeDocx(string $path, array $paragraphs): void
    {
        $body = collect($paragraphs)
            ->map(fn (string $p) => '<w:p><w:r><w:t>' . htmlspecialchars($p, ENT_XML1) . '</w:t></w:r></w:p>')
            ->implode('');

        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>' . $body . '</w:body></w:document>';

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
        $zip->addFromString('word/document.xml', $document);
        $zip->close();
    }

    public function test_uploaded_images_resolve_to_a_root_relative_storage_url(): void
    {
        $tour = $this->tour(['image' => 'tours/uploaded.jpg']);

        // Must be root-relative. Storage::url() builds absolute URLs from
        // APP_URL, which silently points images at the wrong host whenever the
        // site is browsed on another port or domain.
        $this->assertSame('/storage/tours/uploaded.jpg', $tour->image_url);
    }

    public function test_image_urls_never_carry_a_hard_coded_host(): void
    {
        config(['app.url' => 'http://localhost', 'filesystems.disks.public.url' => 'http://localhost/storage']);

        $tour = $this->tour([
            'image' => 'tours/uploaded.jpg',
            'gallery' => ['tours/gallery/a.jpg', '/assets/images/kili.jpg'],
        ]);

        foreach (array_merge([$tour->image_url], $tour->gallery_urls) as $url) {
            $this->assertStringStartsWith('/', $url);
            $this->assertStringNotContainsString('http://', $url);
        }
    }

    public function test_legacy_public_paths_still_resolve(): void
    {
        $tour = $this->tour(['image' => '/assets/images/carousel/lionss_with_her_cub.jpg']);

        $this->assertSame('/assets/images/carousel/lionss_with_her_cub.jpg', $tour->image_url);
    }

    public function test_a_mixed_gallery_resolves_every_entry(): void
    {
        $tour = $this->tour(['gallery' => ['tours/gallery/a.jpg', '/assets/images/kili.jpg']]);

        $urls = $tour->gallery_urls;

        $this->assertSame(['/storage/tours/gallery/a.jpg', '/assets/images/kili.jpg'], $urls);
    }

    public function test_a_missing_image_resolves_to_null_rather_than_breaking(): void
    {
        $this->assertNull($this->tour(['image' => null])->image_url);
        $this->assertSame([], $this->tour(['slug' => 'no-gallery', 'gallery' => null])->gallery_urls);
    }

    public function test_paragraphs_are_extracted_from_a_word_document(): void
    {
        $path = storage_path('app/test-overview.docx');

        $this->makeDocx($path, [
            'The Serengeti is vast and open.',
            '',
            'Your guide repositions daily on live sightings.',
        ]);

        $paragraphs = DocumentParagraphs::fromFile($path, 'docx');

        unlink($path);

        // The blank paragraph is dropped, the two real ones survive in order.
        $this->assertSame([
            'The Serengeti is vast and open.',
            'Your guide repositions daily on live sightings.',
        ], $paragraphs);
    }

    public function test_paragraphs_are_extracted_from_a_plain_text_file(): void
    {
        $path = storage_path('app/test-overview.txt');
        file_put_contents($path, "First paragraph.\r\n\r\nSecond   paragraph.\n");

        $paragraphs = DocumentParagraphs::fromFile($path, 'txt');

        unlink($path);

        $this->assertSame(['First paragraph.', 'Second paragraph.'], $paragraphs);
    }

    public function test_an_unreadable_file_yields_no_paragraphs_instead_of_throwing(): void
    {
        $path = storage_path('app/not-really.docx');
        file_put_contents($path, 'this is not a zip archive');

        $paragraphs = DocumentParagraphs::fromFile($path, 'docx');

        unlink($path);

        $this->assertSame([], $paragraphs);
    }

    public function test_an_uploaded_image_is_rendered_on_the_public_page(): void
    {
        $this->tour(['slug' => 'uploaded-trip', 'name' => 'Uploaded Trip', 'image' => 'tours/hero.jpg']);

        $this->get('/tours/uploaded-trip')
            ->assertOk()
            ->assertSee('src="/storage/tours/hero.jpg"', escape: false);
    }

    public function test_an_external_url_is_left_alone(): void
    {
        $tour = $this->tour(['image' => 'https://cdn.example.com/a.jpg']);

        $this->assertSame('https://cdn.example.com/a.jpg', $tour->image_url);
    }

    public function test_a_tour_with_no_content_still_renders(): void
    {
        // The admin can save a package before writing the overview.
        $this->tour([
            'slug' => 'bare-trip',
            'name' => 'Bare Trip',
            'summary' => null,
            'highlights' => null,
            'itinerary' => null,
            'included' => null,
            'excluded' => null,
            'gallery' => null,
        ]);

        $this->get('/tours/bare-trip')->assertOk()->assertSee('Bare Trip');
    }
}
