<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    public const HEADER = 'header';

    public const BOTTOM = 'bottom';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rail' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * A panel only renders when there is something to put in it — a heading
     * plus at least one rail link. Without that the item is a plain link.
     */
    public function hasPanel(): bool
    {
        return filled($this->panel_heading) && filled($this->rail);
    }

    protected function panelImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => Media::url($this->panel_image));
    }

    /** @return array<int, array{name: string, path: string}> */
    public function railLinks(): array
    {
        return collect($this->rail ?? [])
            ->filter(fn ($link) => filled($link['name'] ?? null))
            ->map(fn ($link) => [
                'name' => $link['name'],
                'path' => $link['path'] ?? '#',
            ])
            ->values()
            ->all();
    }
}
