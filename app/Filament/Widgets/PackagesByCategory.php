<?php

namespace App\Filament\Widgets;

use App\Models\Tour;
use Filament\Widgets\ChartWidget;

class PackagesByCategory extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Packages by category';

    protected ?string $description = 'The shape of what you currently sell.';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Tour::published()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('total', 'category');

        return [
            'datasets' => [
                [
                    'data' => $counts->values()->all(),
                    'backgroundColor' => ['#4a2e1d', '#a97142', '#e5d3b3', '#7d9a6d', '#c98f4b'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $counts->keys()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['position' => 'bottom']]];
    }
}
