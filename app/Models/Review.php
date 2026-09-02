<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Review extends Model
{
    protected $fillable = [
        'name',
        'email',
        'location',
        'photo',
        'tour_slug',
        'tour_name',
        'title',
        'body',
        'rating',
        'rating_guiding',
        'rating_value',
        'travelled_on',
        'source',
        'is_published',
        'is_featured',
    ];

    protected $attributes = [
        'source' => 'website',
        'is_published' => false,
    ];

    protected function casts(): array
    {
        return [
            'travelled_on' => 'date',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'rating' => 'integer',
            'rating_guiding' => 'integer',
            'rating_value' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeAwaiting(Builder $query): Builder
    {
        return $query->where('is_published', false);
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('travelled_on')->orderByDesc('created_at');
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => Media::url($this->photo));
    }

    /** Fallback avatar when no photo was supplied. */
    protected function initials(): Attribute
    {
        return Attribute::get(fn (): string => Str::of($this->name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode(''));
    }

    /**
     * Headline figures for the summary panel.
     *
     * Averages are rounded to one decimal, and null when there is nothing to
     * average — a "0.0 out of 5" on a new site reads as a terrible score.
     *
     * @return array{average: float|null, total: int, guiding: float|null, value: float|null, distribution: array<int, int>}
     */
    public static function summary(): array
    {
        $published = static::published();

        $total = (clone $published)->count();

        $average = fn (string $column) => $total > 0
            ? (($avg = (clone $published)->whereNotNull($column)->avg($column)) !== null
                ? round((float) $avg, 1)
                : null)
            : null;

        $counts = (clone $published)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        return [
            'average' => $average('rating'),
            'total' => $total,
            'guiding' => $average('rating_guiding'),
            'value' => $average('rating_value'),
            // Always five buckets, so the bars render even at zero.
            'distribution' => collect(range(5, 1))
                ->mapWithKeys(fn (int $star) => [$star => (int) $counts->get($star, 0)])
                ->all(),
        ];
    }
}
