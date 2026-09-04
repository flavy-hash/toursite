<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Accommodation extends Model
{
    /** Property types, used for the filter and the admin select. */
    public const TYPES = [
        'Lodge' => 'Lodge',
        'Tented Camp' => 'Tented camp',
        'Mobile Camp' => 'Mobile camp',
        'Hotel' => 'Hotel',
        'Resort' => 'Resort',
        'Guesthouse' => 'Guesthouse',
    ];

    /** Matches the tier on a package, so the two can be paired sensibly. */
    public const LEVELS = [
        'budget' => 'Budget',
        'mid-range' => 'Mid-range',
        'classic' => 'Classic',
        'premium' => 'Premium',
        'luxury' => 'Luxury',
    ];

    public const BOARD = [
        'Full board' => 'Full board',
        'Half board' => 'Half board',
        'Bed and breakfast' => 'Bed and breakfast',
        'All inclusive' => 'All inclusive',
        'Room only' => 'Room only',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'amenities' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'rating' => 'integer',
        ];
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class)
            ->withPivot(['nights', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

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
        return Attribute::get(fn (): ?string => Media::url($this->image));
    }

    /** @return array<int, string> */
    protected function galleryUrls(): Attribute
    {
        return Attribute::get(fn (): array => collect($this->gallery ?? [])
            ->map(fn (string $path) => Media::url($path))
            ->filter()
            ->values()
            ->all());
    }

    public function levelLabel(): string
    {
        return self::LEVELS[$this->level] ?? ucfirst((string) $this->level);
    }
}
