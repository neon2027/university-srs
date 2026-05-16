<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class TicketsByOfficeChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Tickets by Office';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $query = $this->scopedQuery();

        $counts = $query
            ->join('offices', 'tickets.office_id', '=', 'offices.id')
            ->selectRaw('offices.name as office_name, COUNT(*) as count')
            ->groupBy('offices.id', 'offices.name')
            ->orderByDesc('count')
            ->pluck('count', 'office_name');

        return [
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'data' => $counts->values()->all(),
                    'backgroundColor' => 'rgba(14, 165, 233, 0.7)',
                    'borderColor' => 'rgba(14, 165, 233, 1)',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $counts->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function scopedQuery(): Builder
    {
        $query = Ticket::query();

        if (auth()->user()->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = auth()->user()->offices()->pluck('offices.id');
            $query->whereIn('tickets.office_id', $officeIds);
        }

        return $query;
    }
}
