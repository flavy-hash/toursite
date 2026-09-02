<?php

namespace App\Filament\Resources\RegionTours\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

abstract class ListRegionTours extends ListRecords
{
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New package'),
        ];
    }
}
