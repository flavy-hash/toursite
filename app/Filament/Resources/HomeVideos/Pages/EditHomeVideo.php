<?php

namespace App\Filament\Resources\HomeVideos\Pages;

use App\Filament\Resources\HomeVideos\HomeVideoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditHomeVideo extends EditRecord
{
    protected static string $resource = HomeVideoResource::class;

    public function getTitle(): string
    {
        return 'Homepage video';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('watch')
                ->label('Open on YouTube')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => $this->record->watchUrl())
                ->hidden(fn (): bool => ! $this->record->hasValidId())
                ->openUrlInNewTab(),

            Action::make('view_home')
                ->label('View homepage')
                ->icon('heroicon-m-home')
                ->color('gray')
                ->url(fn (): string => route('home'))
                ->openUrlInNewTab(),
        ];
    }

    /** Stay on the page — this is the only screen the resource has. */
    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
