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

    /** Kept as a thin alias; the logic is shared with the navigation. */
    public static function mediaUrl(?string $path): ?string
    {
        return Media::url($path);
    }
}
