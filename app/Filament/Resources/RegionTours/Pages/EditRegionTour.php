<?php

namespace App\Filament\Resources\RegionTours\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

abstract class EditRegionTour extends EditRecord
{
    /** Keeps the package in this section even though the field is disabled. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['region'] = static::getResource()::region();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
