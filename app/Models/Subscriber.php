<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Subscriber extends Model
{
    protected $fillable = ['email', 'source', 'subscribed_at', 'unsubscribed_at'];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function scopeSubscribed(Builder $query): Builder
    {
        return $query->whereNull('unsubscribed_at');
    }

    protected function isSubscribed(): Attribute
    {
        return Attribute::get(fn (): bool => $this->unsubscribed_at === null);
    }

    /**
     * One-click unsubscribe link for the email footer.
     *
     * Signed rather than guessable, so nobody can opt out a stranger, and
     * deliberately without an expiry — an old newsletter must still work.
     */
    public function unsubscribeUrl(): string
    {
        return URL::signedRoute('unsubscribe', ['subscriber' => $this->getKey()]);
    }

    public function unsubscribe(): void
    {
        $this->update(['unsubscribed_at' => now()]);
    }
}
