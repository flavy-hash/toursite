<?php

namespace App\Filament\Resources\RegionTours;

use App\Filament\Resources\Tours\Schemas\TourForm;
use App\Filament\Resources\Tours\Tables\ToursTable;
use App\Models\Tour;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared behaviour for the per-region package sections.
 *
 * Each subclass is a second window onto the same Tour model, narrowed to one
 * region. Anything created inside a section is stamped with that region, so a
 * package added under Kilimanjaro appears on the Kilimanjaro nav link without
 * anyone having to remember to set a field.
 *
 * Filament skips abstract classes during discovery, so this is not registered
 * as a panel resource itself.
 */
abstract class RegionTourResource extends Resource
{
    protected static ?string $model = Tour::class;

    protected static ?string $recordTitleAttribute = 'name';

    /** The value stored in tours.region for this section. */
    abstract public static function region(): string;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('region', static::region());
    }

    public static function form(Schema $schema): Schema
    {
        return TourForm::configure($schema, static::region());
    }

    public static function table(Table $table): Table
    {
        return ToursTable::configure($table);
    }

    /** Package count, so each section shows its size at a glance. */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getModelLabel(): string
    {
        return 'package';
    }

    public static function getPluralModelLabel(): string
    {
        return 'packages';
    }
}
