<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Filament\Resources\Subscribers\SubscriberResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;

    /** Nothing to create — sign-ups only come from the public form. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
