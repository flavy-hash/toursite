<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Media;

class Tour extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'gallery_videos' => 'array',
            'summary' => 'array',
            'highlights' => 'array',
            'itinerary' => 'array',
            'included' => 'array',
            'excluded' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'rating' => 'decimal:1',
        ];
    }

    /** Where the trip stays, in running order. */
    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class)
            ->withPivot(['nights', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /** Only tours the admin has published are ever shown on the public site. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => static::mediaUrl($this->image));
    }

    /** @return array<int, string> */
    protected function galleryUrls(): Attribute
    {
        return Attribute::get(fn (): array => collect($this->gallery ?? [])
            ->map(fn (string $path) => static::mediaUrl($path))
            ->filter()
            ->values()
            ->all());
    }

    /** @return array<int, string> */
    protected function galleryVideoUrls(): Attribute
    {
        return Attribute::get(fn (): array => collect($this->gallery_videos ?? [])
            ->map(fn (string $path) => static::mediaUrl($path))
            ->filter()
            ->values()
            ->all());
    }

    /**
     * Everything in the gallery, videos first, each tagged with its kind so
     * one grid and one viewer can handle both.
     *
     * @return array<int, array{type: string, url: string, mime: ?string}>
     */
    protected function galleryItems(): Attribute
    {
        return Attribute::get(function (): array {
            $videos = collect($this->gallery_video_urls)->map(fn (string $url): array => [
                'type' => 'video',
                'url' => $url,
                'mime' => static::videoMime($url),
            ]);

            $images = collect($this->gallery_urls)->map(fn (string $url): array => [
                'type' => 'image',
                'url' => $url,
                'mime' => null,
            ]);

            return $videos->concat($images)->all();
        });
    }

    /**
     * <source type> for a video, worked out from the extension.
     *
     * Browsers can usually cope without it, but giving it saves them
     * downloading part of a file only to find they cannot play it.
     */
    public static function videoMime(string $url): ?string
    {
        return match (strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: $url, PATHINFO_EXTENSION))) {
            'mp4', 'm4v' => 'video/mp4',
            'webm' => 'video/webm',
            'ogv', 'ogg' => 'video/ogg',
            'mov' => 'video/quicktime',
            default => null,
        };
    }

    /** Kept as a thin alias; the logic is shared with the navigation. */
    public static function mediaUrl(?string $path): ?string
    {
        return Media::url($path);
    }
}
