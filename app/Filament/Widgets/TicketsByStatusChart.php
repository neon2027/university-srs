<?php

namespace App\Filament\Widgets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class TicketsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Tickets by Status';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $counts = $this->scopedQuery()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $statuses = TicketStatus::cases();

        return [
            'datasets' => [
                [
                    'data' => collect($statuses)->map(fn ($s) => $counts[$s->value] ?? 0)->values()->all(),
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.85)',  // Pending — amber
                        'rgba(96, 165, 250, 0.85)',  // Assigned — blue
                        'rgba(14, 165, 233, 0.85)',  // InProgress — sky
                        'rgba(156, 163, 175, 0.85)', // OnHold — gray
                        'rgba(129, 140, 248, 0.85)', // Forwarded — indigo
                        'rgba(34, 197, 94, 0.85)',   // Resolved — green
                        'rgba(16, 185, 129, 0.85)',  // Closed — emerald
                        'rgba(239, 68, 68, 0.85)',   // Cancelled — red
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => collect($statuses)->map(fn ($s) => $s->label())->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
