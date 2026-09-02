<?php

namespace App\Filament\Resources\RegionTours\SouthernCircuit;

use App\Filament\Resources\RegionTours\RegionTourResource;
use App\Filament\Resources\RegionTours\SouthernCircuit\Pages\CreateSouthernCircuitTour;
use App\Filament\Resources\RegionTours\SouthernCircuit\Pages\EditSouthernCircuitTour;
use App\Filament\Resources\RegionTours\SouthernCircuit\Pages\ListSouthernCircuitTours;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SouthernCircuitTourResource extends RegionTourResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Tours by region';

    protected static ?string $navigationLabel = 'Southern Circuit';

    protected static ?string $slug = 'southern-circuit-tours';

    protected static ?int $navigationSort = 12;

    public static function region(): string
    {
        return 'southern';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSouthernCircuitTours::route('/'),
            'create' => CreateSouthernCircuitTour::route('/create'),
            'edit' => EditSouthernCircuitTour::route('/{record}/edit'),
        ];
    }
}
