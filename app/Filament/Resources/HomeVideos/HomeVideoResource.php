<?php

namespace App\Filament\Resources\HomeVideos;

use App\Filament\Resources\HomeVideos\Pages\EditHomeVideo;
use App\Filament\Resources\HomeVideos\Schemas\HomeVideoForm;
use App\Models\HomeVideo;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * The YouTube panel on the homepage.
 *
 * A single record, so the sidebar link opens the editor directly rather than
 * a list of one row. There is nothing to create or delete.
 */
class HomeVideoResource extends Resource
{
    protected static ?string $model = HomeVideo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?string $navigationLabel = 'Homepage Video';

    protected static ?string $modelLabel = 'homepage video';

    protected static ?int $navigationSort = 24;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return HomeVideoForm::configure($schema);
    }

    /** Straight into the editor; a list of one row helps nobody. */
    public static function getNavigationUrl(array $parameters = []): string
    {
        return static::editUrl();
    }

    /**
     * Built by hand because Filament's own implementation bails out for any
     * resource without an index page:
     *
     *     if (! static::hasPage('index')) { return []; }
     *
     * This one deliberately has only an edit screen, so without this override
     * it is registered and routable but never appears in the sidebar.
     *
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->key(static::class)
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.*'))
                ->sort(static::getNavigationSort())
                ->url(static::getNavigationUrl()),
        ];
    }

    /**
     * With no index page, Filament's breadcrumbs and post-save redirects would
     * throw looking for one. Both are pointed back at the editor instead.
     */
    public static function getIndexUrl(
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?Model $tenant = null,
        bool $shouldGuessMissingParameters = false,
    ): string {
        return static::editUrl();
    }

    /** The single record's edit screen, creating the row if it is missing. */
    private static function editUrl(): string
    {
        $video = HomeVideo::first() ?? HomeVideo::create(['is_published' => false]);

        return static::getUrl('edit', ['record' => $video]);
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditHomeVideo::route('/{record}/edit'),
        ];
    }
}
