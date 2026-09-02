<?php

namespace App\Filament\Resources\RegionTours\Kilimanjaro;

use App\Filament\Resources\RegionTours\RegionTourResource;
use App\Filament\Resources\RegionTours\Kilimanjaro\Pages\CreateKilimanjaroTour;
use App\Filament\Resources\RegionTours\Kilimanjaro\Pages\EditKilimanjaroTour;
use App\Filament\Resources\RegionTours\Kilimanjaro\Pages\ListKilimanjaroTours;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class KilimanjaroTourResource extends RegionTourResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = 'Tours by region';

    protected static ?string $navigationLabel = 'Kilimanjaro';

    protected static ?string $slug = 'kilimanjaro-tours';

    protected static ?int $navigationSort = 10;

    public static function region(): string
    {
        return 'kilimanjaro';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKilimanjaroTours::route('/'),
            'create' => CreateKilimanjaroTour::route('/create'),
            'edit' => EditKilimanjaroTour::route('/{record}/edit'),
        ];
    }
}
