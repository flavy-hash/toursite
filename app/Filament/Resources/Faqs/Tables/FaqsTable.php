<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Drag to reorder; the same order the FAQ page renders in.
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            // Anything still waiting for an answer floats to the top: that is
            // the work, and it should not be buried under answered entries.
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByRaw(
                "CASE WHEN answer IS NULL OR answer = '' THEN 0 ELSE 1 END"
            ))
            ->columns([
                TextColumn::make('question')
                    ->searchable()
                    ->wrap()
                    ->weight('semibold')
                    // The answer under the question, so staff can scan the pair
                    // without opening every row.
                    ->description(fn (Faq $record): string => $record->isAnswered()
                        ? str($record->answer)->squish()->limit(110)->value()
                        : 'Not answered yet'),

                // Makes a visitor's unanswered question obvious at a glance.
                TextColumn::make('asked_at')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Faq $record): string => match (true) {
                        ! $record->isAnswered() => 'Needs an answer',
                        $record->wasAskedByAVisitor() => 'Asked by a visitor',
                        default => 'Written by us',
                    })
                    ->color(fn (Faq $record): string => match (true) {
                        ! $record->isAnswered() => 'warning',
                        $record->wasAskedByAVisitor() => 'info',
                        default => 'gray',
                    })
                    ->description(fn (Faq $record): ?string => $record->asked_at?->diffForHumans()),

                TextColumn::make('category')
                    ->label('Section')
                    ->badge()
                    ->color('gray')
                    ->placeholder(Faq::GENERAL)
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_published')->label('Live')->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Section')
                    // Built from what is actually in use, not a fixed list.
                    ->options(fn (): array => Faq::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),

                Filter::make('needs_answer')
                    ->label('Needs an answer')
                    ->query(fn (Builder $query) => $query->awaiting())
                    ->toggle(),

                Filter::make('from_visitors')
                    ->label('Asked by visitors')
                    ->query(fn (Builder $query) => $query->fromVisitors())
                    ->toggle(),

                TernaryFilter::make('is_published')
                    ->label('Visibility')
                    ->trueLabel('On the site')
                    ->falseLabel('Hidden'),
            ])
            ->recordActions([
                self::answerAction(),
                self::visibilityAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::bulkVisibilityAction(true, 'Show on the site'),
                    self::bulkVisibilityAction(false, 'Hide'),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No questions yet')
            ->emptyStateDescription('The FAQ page shows nothing until a question is added here.');
    }

    /**
     * Answer a visitor's question and put it live in one step.
     *
     * The alternative is opening the edit screen, typing, scrolling to the
     * publish toggle and saving — four actions for what is really one.
     */
    private static function answerAction(): Action
    {
        return Action::make('answer')
            ->label('Answer')
            ->icon('heroicon-m-pencil-square')
            ->color('warning')
            ->button()
            ->visible(fn (Faq $record): bool => ! $record->isAnswered())
            ->modalHeading('Answer this question')
            ->modalDescription(fn (Faq $record): string => $record->question)
            ->modalSubmitActionLabel('Save answer')
            ->schema([
                Textarea::make('answer')
                    ->label('Your answer')
                    ->required()
                    ->rows(7)
                    ->helperText('Leave a blank line between paragraphs and they render as separate paragraphs.'),

                TextInput::make('category')
                    ->label('Section heading')
                    ->maxLength(80)
                    ->datalist(Faq::SUGGESTED_CATEGORIES)
                    ->placeholder(Faq::GENERAL)
                    ->helperText('Optional. Groups this question with others under the same heading.'),

                Toggle::make('is_published')
                    ->label('Put it on the FAQ page straight away')
                    ->default(true),
            ])
            ->action(function (Faq $record, array $data): void {
                $record->update([
                    'answer' => $data['answer'],
                    'category' => $data['category'] ?: null,
                    'is_published' => (bool) $data['is_published'],
                ]);

                Notification::make()
                    ->title($record->is_published ? 'Answered and published' : 'Answer saved')
                    ->body($record->is_published
                        ? 'It is on the FAQ page now.'
                        : 'Publish it when you are ready.')
                    ->success()
                    ->send();
            });
    }

    /** Take a question down without losing the answer someone wrote. */
    private static function visibilityAction(): Action
    {
        return Action::make('toggle_published')
            // Hidden while unanswered: the Answer action covers that case, and
            // publishing a blank answer would put an empty row on the page.
            ->visible(fn (Faq $record): bool => $record->isAnswered())
            ->label(fn (Faq $record): string => $record->is_published ? 'Hide' : 'Show')
            ->icon(fn (Faq $record): string => $record->is_published
                ? 'heroicon-m-eye-slash'
                : 'heroicon-m-eye')
            ->color('gray')
            ->action(function (Faq $record): void {
                $record->update(['is_published' => ! $record->is_published]);

                Notification::make()
                    ->title($record->is_published ? 'Now on the FAQ page' : 'Hidden from the FAQ page')
                    ->success()
                    ->send();
            });
    }

    private static function bulkVisibilityAction(bool $published, string $label): BulkAction
    {
        return BulkAction::make($published ? 'bulk_show' : 'bulk_hide')
            ->label($label)
            ->icon($published ? 'heroicon-m-eye' : 'heroicon-m-eye-slash')
            ->color('gray')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) use ($published, $label): void {
                $records->each->update(['is_published' => $published]);

                Notification::make()
                    ->title($label)
                    ->body($records->count() . ' ' . str('question')->plural($records->count()) . ' updated.')
                    ->success()
                    ->send();
            });
    }
}
