<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Faq extends Model
{
    /** Questions with no category of their own are shown under this heading. */
    public const GENERAL = 'General';

    /**
     * Offered as suggestions in the admin form. Staff can type anything else;
     * these only save them inventing a house style from scratch.
     *
     * @var array<int, string>
     */
    public const SUGGESTED_CATEGORIES = [
        'Before you book',
        'Planning your trip',
        'On safari',
        'Kilimanjaro',
        'Zanzibar',
        'Health & safety',
        'Payments & cancellation',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'asked_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Questions a visitor asked that nobody has answered yet.
     *
     * Answered-but-unpublished is a different thing — someone has written a
     * reply and is sitting on it — so this looks at the answer, not the
     * publish flag.
     */
    public function scopeAwaiting(Builder $query): Builder
    {
        // Grouped, so the "or" cannot escape and swallow whatever conditions
        // the caller has already applied.
        return $query->where(fn (Builder $q) => $q->whereNull('answer')->orWhere('answer', ''));
    }

    /** Came in through the form on /faq rather than typed by staff. */
    public function scopeFromVisitors(Builder $query): Builder
    {
        return $query->whereNotNull('asked_at');
    }

    public function isAnswered(): bool
    {
        return filled($this->answer);
    }

    public function wasAskedByAVisitor(): bool
    {
        return $this->asked_at !== null;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Published questions under their headings, in the admin's order.
     *
     * Categories appear in the order their first question does, so dragging a
     * question to the top of the list moves its whole section up — which is
     * what someone reordering the list expects to happen.
     *
     * @return Collection<string, Collection<int, self>>
     */
    public static function grouped(): Collection
    {
        return static::published()
            // An unanswered question must never reach the page, even if the
            // publish flag is somehow set on it.
            ->whereNotNull('answer')
            ->where('answer', '!=', '')
            ->ordered()
            ->get()
            ->groupBy(fn (self $faq): string => $faq->category ?: self::GENERAL);
    }
}
