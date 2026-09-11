<?php

namespace App\Filament\Resources\HomeVideos\Schemas;

use App\Models\HomeVideo;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class HomeVideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('The video')
                ->description('Paste the video ID, or the whole YouTube link — either works.')
                ->schema([
                    TextInput::make('youtube_id')
                        ->label('YouTube video')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('pGx9BTRx3-w')
                        ->live(onBlur: true)
                        ->helperText('The ID is the part after "v=" in a YouTube address. '
                            . 'You can also paste the full link — youtube.com/watch?v=..., '
                            . 'youtu.be/..., or a Shorts link — and we will pull the ID out of it.')
                        // Reject anything that is not a YouTube id once the URL
                        // has been stripped, so a typo is caught here rather
                        // than showing a broken player on the homepage.
                        ->rule(function () {
                            return function (string $attribute, $value, $fail) {
                                if (! preg_match('#^[A-Za-z0-9_-]{11}$#', HomeVideo::extractId($value) ?? '')) {
                                    $fail('That does not look like a YouTube video. Paste the '
                                        . 'video link or its 11-character ID.');
                                }
                            };
                        }),

                    // Immediate confirmation that the right video was pasted —
                    // far quicker than saving and loading the homepage.
                    Text::make(fn (?HomeVideo $record, $get): string => ($id = HomeVideo::extractId($get('youtube_id')))
                        && preg_match('#^[A-Za-z0-9_-]{11}$#', $id)
                            ? 'Saved as: ' . $id . '  ·  youtube.com/watch?v=' . $id
                            : 'No video set yet.')
                        ->color('gray'),
                ]),

            Section::make('Wording around the player')
                ->columns(2)
                ->schema([
                    TextInput::make('eyebrow')
                        ->label('Small line above the heading')
                        ->maxLength(80)
                        ->placeholder('FROM OUR CHANNEL'),

                    TextInput::make('heading')
                        ->label('Section heading')
                        ->maxLength(160)
                        ->placeholder('Take a glimpse into the safari'),

                    TextInput::make('video_title')
                        ->label('Caption title')
                        ->maxLength(160)
                        ->placeholder('One day on the Serengeti')
                        ->helperText('Shown in the dark bar under the player.'),

                    TextInput::make('caption')
                        ->label('Caption detail')
                        ->maxLength(200)
                        ->placeholder('FILMED BY OUR GUIDES')
                        ->helperText('The small line beneath the caption title.'),
                ]),

            Section::make('Publishing')
                ->schema([
                    Toggle::make('is_published')
                        ->label('Show this section on the homepage')
                        ->helperText('Turn this off and the whole video section disappears from '
                            . 'the homepage — no empty player is left behind.'),
                ]),
        ]);
    }
}
