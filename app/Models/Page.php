<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * An editable content page.
 *
 * Slugs map to routes declared in routes/web.php, not the other way round —
 * see the migration. The two that exist are listed here so the panel and the
 * seeder cannot drift apart.
 */
class Page extends Model
{
    public const ABOUT = 'about';

    public const TEAM = 'team';

    public const FAQ = 'faq';

    /**
     * Slug to route name. One map, so a page added here shows the right
     * address in the panel, the right link in the sitemap and the right
     * "View page" target without four separate lists agreeing by luck.
     *
     * @var array<string, string>
     */
    public const ROUTES = [
        self::ABOUT => 'about',
        self::TEAM => 'about.team',
        self::FAQ => 'faq',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    protected function heroImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => Media::url($this->hero_image));
    }

    /**
     * Body sections with their images already resolved, and any blank ones
     * dropped — a repeater row left empty should not render as a gap.
     *
     * @return array<int, array{heading: ?string, body: ?string, image: ?string}>
     */
    protected function bodySections(): Attribute
    {
        return Attribute::get(fn (): array => collect($this->sections ?? [])
            ->map(fn (array $section): array => [
                'heading' => $section['heading'] ?? null,
                'body' => $section['body'] ?? null,
                'image' => Media::url($section['image'] ?? null),
            ])
            ->filter(fn (array $section): bool => filled($section['heading'])
                || filled($section['body'])
                || filled($section['image']))
            ->values()
            ->all());
    }

    /**
     * The address this page lives at.
     *
     * Null for a record whose slug has no route — which should not happen,
     * since pages cannot be created in the panel, but the panel must not blow
     * up if one is ever inserted by hand.
     */
    public function url(): ?string
    {
        $route = self::ROUTES[$this->slug] ?? null;

        return $route ? route($route) : null;
    }

    /** The path alone, for showing staff where a page lives. */
    public function path(): ?string
    {
        $url = $this->url();

        return $url ? (parse_url($url, PHP_URL_PATH) ?: $url) : null;
    }

    /** Falls back to the page title so a <title> is never empty. */
    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /** Falls back to the intro, trimmed to a sensible length for a snippet. */
    public function metaDescription(): ?string
    {
        return $this->meta_description
            ?: ($this->intro ? str($this->intro)->squish()->limit(155)->value() : null);
    }
}
