<?php

namespace App\Filament\Widgets;

use App\Models\Inquiry;
use Filament\Widgets\ChartWidget;

class EnquiriesByPackage extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Enquiries by package';

    protected ?string $description = 'Which trips people are actually asking about.';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = Inquiry::query()
            ->selectRaw('COALESCE(tour_name, ?) as label, COUNT(*) as total', ['General enquiry'])
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'label');

        return [
            'datasets' => [
                [
                    'label' => 'Enquiries',
                    'data' => $counts->values()->all(),
                    'backgroundColor' => '#a97142',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $counts->keys()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            // Package names are long; horizontal bars keep them readable.
            'indexAxis' => 'y',
            'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'plugins' => ['legend' => ['display' => false]],
        ];
    }
}
