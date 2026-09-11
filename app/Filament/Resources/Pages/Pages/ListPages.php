<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    /** No create action — each page's slug has to match a declared route. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
