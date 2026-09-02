<?php

namespace App\Filament\Resources\Subscribers\Tables;

use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->weight('semibold'),

                TextColumn::make('subscribed_at')
                    ->label('Joined')
                    ->dateTime('j M Y, H:i')
                    ->description(fn (Subscriber $record) => $record->subscribed_at?->diffForHumans())
                    ->sortable(),

                TextColumn::make('source')->badge()->color('gray'),

                TextColumn::make('unsubscribed_at')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Unsubscribed' : 'Subscribed')
                    ->color(fn ($state) => $state ? 'danger' : 'success')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('subscribed')
                    ->label('Subscribed only')
                    ->default()
                    ->query(fn (Builder $query) => $query->whereNull('unsubscribed_at')),
            ])
            ->recordActions([
                // Kept rather than deleted, so a re-subscribe is traceable and
                // an address is never silently mailed again after opting out.
                Action::make('toggle')
                    ->label(fn (Subscriber $record) => $record->is_subscribed ? 'Unsubscribe' : 'Resubscribe')
                    ->icon(fn (Subscriber $record) => $record->is_subscribed ? 'heroicon-m-x-circle' : 'heroicon-m-arrow-path')
                    ->color(fn (Subscriber $record) => $record->is_subscribed ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Subscriber $record) => $record->update([
                        'unsubscribed_at' => $record->is_subscribed ? now() : null,
                        'subscribed_at' => $record->is_subscribed ? $record->subscribed_at : now(),
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
