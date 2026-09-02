<?php

namespace App\Filament\Widgets;

use App\Models\Inquiry;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EnquiriesOverTime extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Enquiries over time';

    protected ?string $description = 'Daily enquiries for the selected period.';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 30);

        $counts = Inquiry::query()
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->get(['created_at'])
            ->countBy(fn (Inquiry $inquiry) => $inquiry->created_at->toDateString());

        $dates = collect(range($days - 1, 0))
            ->map(fn (int $ago) => Carbon::today()->subDays($ago));

        return [
            'datasets' => [
                [
                    'label' => 'Enquiries',
                    'data' => $dates->map(fn (Carbon $d) => $counts->get($d->toDateString(), 0))->all(),
                    'borderColor' => '#e5d3b3',
                    'backgroundColor' => 'rgba(229, 211, 179, 0.18)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            // A 90-day axis is unreadable with every date, so thin the labels.
            'labels' => $dates->map(fn (Carbon $d, int $i) => $days > 31 && $i % 7 !== 0
                ? ''
                : $d->format('j M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                // Enquiry counts are whole numbers; fractional ticks are noise.
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'plugins' => ['legend' => ['display' => false]],
        ];
    }
}
