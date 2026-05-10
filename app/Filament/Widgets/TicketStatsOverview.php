<?php

namespace App\Filament\Widgets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $query = Ticket::query();

        if (auth()->user()->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = auth()->user()->offices()->pluck('offices.id');
            $query->whereIn('office_id', $officeIds);
        }

        return [
            Stat::make('Pending', (clone $query)->where('status', TicketStatus::Pending)->count())
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('In Progress', (clone $query)->where('status', TicketStatus::InProgress)->count())
                ->color('info')
                ->icon('heroicon-o-arrow-path'),
            Stat::make('Forwarded', (clone $query)->where('status', TicketStatus::Forwarded)->count())
                ->color('primary')
                ->icon('heroicon-o-arrow-right-circle'),
            Stat::make('Resolved Today', (clone $query)
                ->where('status', TicketStatus::Resolved)
                ->whereDate('resolved_at', today())
                ->count())
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
