<?php

namespace App\Filament\Resources\TeamMembers\Tables;

use App\Models\TeamMember;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Drag to reorder; the handle writes straight to sort_order, which
            // is the same order the team page renders in.
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->imageHeight(44),

                TextColumn::make('name')->searchable()->sortable()->weight('semibold'),

                TextColumn::make('role')->searchable()->placeholder('—'),

                TextColumn::make('email')->searchable()->copyable()->placeholder('—')->toggleable(),

                IconColumn::make('is_published')->label('Live')->boolean(),
            ])
            ->recordActions([
                self::visibilityAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No one added yet')
            ->emptyStateDescription('The team section only appears on /about/team once someone is added here.');
    }

    /** One click to take someone off the page without deleting their record. */
    private static function visibilityAction(): Action
    {
        return Action::make('toggle_published')
            ->label(fn (TeamMember $record): string => $record->is_published ? 'Hide' : 'Show')
            ->icon(fn (TeamMember $record): string => $record->is_published
                ? 'heroicon-m-eye-slash'
                : 'heroicon-m-eye')
            ->color('gray')
            ->action(function (TeamMember $record): void {
                $record->update(['is_published' => ! $record->is_published]);

                Notification::make()
                    ->title($record->is_published
                        ? $record->name . ' is now on the team page.'
                        : $record->name . ' is hidden from the team page.')
                    ->success()
                    ->send();
            });
    }
}
