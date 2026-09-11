<?php

namespace App\Models;

use App\Observers\InquiryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(InquiryObserver::class)]
class Inquiry extends Model
{
    public const NEW = 'new';

    public const CONTACTED = 'contacted';

    public const QUOTED = 'quoted';

    public const BOOKED = 'booked';

    public const CLOSED = 'closed';

    /**
     * The pipeline, in order. Labels and badge colours live here so the form,
     * the table and the dashboard cannot drift apart.
     *
     * @var array<string, array{label: string, colour: string, icon: string}>
     */
    public const STATUSES = [
        self::NEW => ['label' => 'New', 'colour' => 'warning', 'icon' => 'heroicon-m-inbox-arrow-down'],
        self::CONTACTED => ['label' => 'Contacted', 'colour' => 'info', 'icon' => 'heroicon-m-chat-bubble-left-right'],
        self::QUOTED => ['label' => 'Quoted', 'colour' => 'primary', 'icon' => 'heroicon-m-document-text'],
        self::BOOKED => ['label' => 'Booked', 'colour' => 'success', 'icon' => 'heroicon-m-check-badge'],
        self::CLOSED => ['label' => 'Closed', 'colour' => 'gray', 'icon' => 'heroicon-m-archive-box'],
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'tour_slug',
        'tour_name',
        'travel_date',
        'travellers',
        'message',

        // Set by staff in the admin panel, not by the public form.
        'status',
    ];

    /**
     * Mirrors the column default so a newly created record reports its status
     * immediately, rather than null until it is reloaded from the database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::NEW,
    ];

    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
            'confirmation_sent_at' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return array_map(fn (array $status) => $status['label'], self::STATUSES);
    }

    public static function statusColour(?string $status): string
    {
        return self::STATUSES[$status]['colour'] ?? 'gray';
    }

    public static function statusLabel(?string $status): string
    {
        return self::STATUSES[$status]['label'] ?? ucfirst((string) $status);
    }

    public function scopeAwaiting(Builder $query): Builder
    {
        return $query->where('status', self::NEW);
    }

    public function isBooked(): bool
    {
        return $this->status === self::BOOKED;
    }

    /** Whether the guest has been emailed that their booking is confirmed. */
    public function confirmationWasSent(): bool
    {
        return $this->confirmation_sent_at !== null;
    }

    /**
     * Booked, but the guest has not been told. Normally momentary; it persists
     * when the mail server was unreachable at the time, which is exactly when
     * staff need to see it.
     */
    public function awaitsConfirmationEmail(): bool
    {
        return $this->isBooked() && ! $this->confirmationWasSent();
    }
}
