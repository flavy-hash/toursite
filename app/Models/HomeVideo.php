<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * The YouTube panel on the homepage.
 *
 * One row. There is no list screen in the panel — the resource opens straight
 * into editing it — so the site never has to choose between two of these.
 */
class HomeVideo extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * The video to show, or null when there is none to show.
     *
     * The homepage skips the whole section on null rather than rendering an
     * empty player, so an unpublished video simply disappears.
     */
    public static function current(): ?self
    {
        return static::query()->where('is_published', true)->first();
    }

    /**
     * Accepts an id or any YouTube URL and stores the id either way.
     *
     * Asking someone to find the id inside a share link is a needless
     * chore, and the commonest way to break this field.
     */
    protected function youtubeId(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => static::extractId($value));
    }

    /**
     * Pulls the video id out of whatever was pasted.
     *
     * Handles watch links, youtu.be share links, embed and shorts URLs, with
     * or without extra query parameters. Anything already looking like a bare
     * id is returned untouched.
     */
    public static function extractId(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // A bare id: 11 characters of the YouTube alphabet.
        if (preg_match('#^[A-Za-z0-9_-]{11}$#', $value)) {
            return $value;
        }

        $patterns = [
            '#[?&]v=([A-Za-z0-9_-]{11})#',          // watch?v=ID
            '#youtu\.be/([A-Za-z0-9_-]{11})#',      // youtu.be/ID
            '#/embed/([A-Za-z0-9_-]{11})#',         // /embed/ID
            '#/shorts/([A-Za-z0-9_-]{11})#',        // /shorts/ID
            '#/live/([A-Za-z0-9_-]{11})#',          // /live/ID
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches)) {
                return $matches[1];
            }
        }

        // Unrecognised: keep what was typed so the admin can see and correct
        // it, rather than silently discarding their input.
        return $value;
    }

    /** rel=0 keeps YouTube's "up next" suggestions to this channel. */
    public function embedUrl(): string
    {
        return 'https://www.youtube.com/embed/' . $this->youtube_id . '?rel=0';
    }

    public function watchUrl(): string
    {
        return 'https://www.youtube.com/watch?v=' . $this->youtube_id;
    }

    /** Used as the poster and as the social share image for the section. */
    public function thumbnailUrl(): string
    {
        return 'https://i.ytimg.com/vi/' . $this->youtube_id . '/maxresdefault.jpg';
    }

    /** True only when the stored value is a plausible YouTube id. */
    public function hasValidId(): bool
    {
        return (bool) preg_match('#^[A-Za-z0-9_-]{11}$#', (string) $this->youtube_id);
    }
}
