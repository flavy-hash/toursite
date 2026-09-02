<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestEnquiries extends TableWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Latest enquiries';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Inquiry::query()->latest()->limit(8))
            ->paginated(false)
            ->emptyStateHeading('No enquiries yet')
            ->emptyStateDescription('They will appear here as soon as the booking form is used.')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->tooltip(fn (Inquiry $record) => $record->created_at?->format('j M Y, H:i')),

                TextColumn::make('name')->weight('semibold')->searchable(),

                TextColumn::make('email')->copyable()->copyMessage('Email copied'),

                TextColumn::make('tour_name')
                    ->label('Package')
                    ->placeholder('General enquiry')
                    ->wrap(),

                TextColumn::make('travellers')->label('Pax')->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Inquiry::statusLabel($state))
                    ->color(fn (?string $state) => Inquiry::statusColour($state)),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Inquiry $record) => InquiryResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
