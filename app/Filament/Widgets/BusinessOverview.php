<?php

namespace App\Filament\Widgets;

use App\Models\Inquiry;
use App\Models\Tour;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BusinessOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $newEnquiries = Inquiry::awaiting()->count();
        $thisMonth = Inquiry::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
        $lastMonth = Inquiry::whereBetween('created_at', [
            now()->subMonthNoOverflow()->startOfMonth(),
            now()->subMonthNoOverflow()->endOfMonth(),
        ])->count();

        return [
            Stat::make('Enquiries this month', $thisMonth)
                ->description($this->movement($thisMonth, $lastMonth))
                ->descriptionIcon($thisMonth >= $lastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($thisMonth >= $lastMonth ? 'success' : 'danger')
                ->chart($this->enquiriesPerDay(14)),

            Stat::make('Awaiting reply', $newEnquiries)
                ->description($newEnquiries > 0 ? 'Still marked as new' : 'All caught up')
                ->descriptionIcon($newEnquiries > 0 ? 'heroicon-m-inbox-arrow-down' : 'heroicon-m-check-circle')
                ->color($newEnquiries > 0 ? 'warning' : 'success'),

            Stat::make('Booked', Inquiry::where('status', Inquiry::BOOKED)->count())
                ->description('Enquiries that converted')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Live packages', Tour::published()->count())
                ->description(Tour::count() . ' in total, ' . Tour::where('is_published', false)->count() . ' unpublished')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('primary'),
        ];
    }

    private function movement(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? 'First enquiries this month' : 'None yet this month';
        }

        $change = round((($current - $previous) / $previous) * 100);

        return ($change >= 0 ? '+' : '') . $change . '% vs last month';
    }

    /** @return array<int, int> Daily counts, oldest first, for the sparkline. */
    private function enquiriesPerDay(int $days): array
    {
        $counts = Inquiry::query()
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->get(['created_at'])
            ->countBy(fn (Inquiry $inquiry) => $inquiry->created_at->toDateString());

        return collect(range($days - 1, 0))
            ->map(fn (int $ago) => $counts->get(Carbon::today()->subDays($ago)->toDateString(), 0))
            ->all();
    }
}
