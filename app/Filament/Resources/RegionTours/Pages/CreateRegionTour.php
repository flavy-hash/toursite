<?php

namespace App\Filament\Resources\RegionTours\Pages;

use Filament\Resources\Pages\CreateRecord;

abstract class CreateRegionTour extends CreateRecord
{
    /**
     * Stamp the section's region on the new package.
     *
     * The field is disabled in the form, so this is what actually guarantees
     * the value — a disabled input cannot be trusted to arrive.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['region'] = static::getResource()::region();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
