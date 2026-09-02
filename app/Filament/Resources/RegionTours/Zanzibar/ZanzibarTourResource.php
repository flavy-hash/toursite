<?php

namespace App\Filament\Resources\RegionTours\Zanzibar;

use App\Filament\Resources\RegionTours\RegionTourResource;
use App\Filament\Resources\RegionTours\Zanzibar\Pages\CreateZanzibarTour;
use App\Filament\Resources\RegionTours\Zanzibar\Pages\EditZanzibarTour;
use App\Filament\Resources\RegionTours\Zanzibar\Pages\ListZanzibarTours;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ZanzibarTourResource extends RegionTourResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSun;

    protected static string|UnitEnum|null $navigationGroup = 'Tours by region';

    protected static ?string $navigationLabel = 'Zanzibar';

    protected static ?string $slug = 'zanzibar-tours';

    protected static ?int $navigationSort = 11;

    public static function region(): string
    {
        return 'zanzibar';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListZanzibarTours::route('/'),
            'create' => CreateZanzibarTour::route('/create'),
            'edit' => EditZanzibarTour::route('/{record}/edit'),
        ];
    }
}
