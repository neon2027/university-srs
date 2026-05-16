<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class TicketVolumeChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Ticket Volume';

    protected ?string $maxHeight = '280px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? '30');
        $start = now()->subDays($days - 1)->startOfDay();

        $query = $this->scopedQuery();

        $counts = $query
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('M j');
            $data[] = $counts[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tickets Created',
                    'data' => $data,
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'borderColor' => 'rgba(14, 165, 233, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function scopedQuery(): Builder
    {
        $query = Ticket::query();

        if (auth()->user()->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = auth()->user()->offices()->pluck('offices.id');
            $query->whereIn('office_id', $officeIds);
        }

        return $query;
    }
}
