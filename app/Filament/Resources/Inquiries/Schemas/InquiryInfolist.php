<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use App\Models\Inquiry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Read-only view of one enquiry — everything the visitor sent, laid out to be
 * read rather than edited.
 */
class InquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Who')
                ->columns(3)
                ->schema([
                    TextEntry::make('name'),

                    TextEntry::make('email')
                        ->copyable()
                        ->copyMessage('Email copied')
                        ->url(fn (Inquiry $record) => 'mailto:' . $record->email),

                    TextEntry::make('phone')
                        ->placeholder('Not given')
                        ->copyable()
                        // tel: needs the number without spaces.
                        ->url(fn (Inquiry $record) => $record->phone
                            ? 'tel:' . preg_replace('/\s+/', '', $record->phone)
                            : null),
                ]),

            Section::make('The trip')
                ->columns(3)
                ->schema([
                    TextEntry::make('tour_name')
                        ->label('Package')
                        ->placeholder('General enquiry'),

                    TextEntry::make('travel_date')
                        ->label('Preferred departure')
                        ->date('j F Y')
                        ->placeholder('Flexible'),

                    TextEntry::make('travellers')->label('Travellers'),
                ]),

            Section::make('Message')
                ->schema([
                    TextEntry::make('message')
                        ->hiddenLabel()
                        ->placeholder('No message left')
                        ->columnSpanFull(),
                ]),

            Section::make('Handling')
                ->columns(2)
                ->schema([
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (?string $state) => Inquiry::statusLabel($state))
                        ->color(fn (?string $state) => Inquiry::statusColour($state)),

                    TextEntry::make('created_at')
                        ->label('Received')
                        ->dateTime('j F Y, H:i')
                        ->since()
                        ->tooltip(fn (Inquiry $record) => $record->created_at?->format('j F Y, H:i')),
                ]),
        ]);
    }
}
