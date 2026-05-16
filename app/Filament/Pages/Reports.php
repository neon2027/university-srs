<?php

namespace App\Filament\Pages;

use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketRating;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class Reports extends Page
{
    protected string $view = 'filament.pages.reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Tickets';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Reports';

    public ?int $officeId = null;

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $generated = false;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();

        $offices = $this->accessibleOffices();
        if ($offices->count() === 1) {
            $this->officeId = $offices->first()->id;
        }
    }

    public function generate(): void
    {
        $this->validate([
            'officeId' => 'required|exists:offices,id',
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
        ]);

        $this->generated = true;
    }

    public function accessibleOffices(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return Office::orderBy('name')->get();
        }

        return $user->offices()->orderBy('name')->get();
    }

    public function getReportData(): array
    {
        if (! $this->generated || ! $this->officeId) {
            return [];
        }

        $office = Office::find($this->officeId);
        $from = $this->dateFrom;
        $to = $this->dateTo;

        $baseQuery = fn () => Ticket::where('office_id', $this->officeId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);

        $totalTickets = $baseQuery()->count();
        $resolvedTickets = $baseQuery()->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count();
        $pendingTickets = $baseQuery()->where('status', TicketStatus::Pending)->count();
        $inProgressTickets = $baseQuery()->whereIn('status', [TicketStatus::Assigned, TicketStatus::InProgress])->count();
        $cancelledTickets = $baseQuery()->where('status', TicketStatus::Cancelled)->count();

        $avgResolutionHours = $baseQuery()
            ->whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at'])
            ->avg(fn ($t) => $t->created_at->diffInHours($t->resolved_at)) ?: null;

        $ratingQuery = fn () => TicketRating::whereHas('ticket', fn ($q) => $q
            ->where('office_id', $this->officeId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']));

        $totalRatings = $ratingQuery()->count();
        $avgOverall = $totalRatings ? round($ratingQuery()->avg('overall_rating'), 2) : null;
        $avgService = $totalRatings ? round($ratingQuery()->avg('service_rating'), 2) : null;
        $avgStaff = $totalRatings ? round($ratingQuery()->whereNotNull('staff_rating')->avg('staff_rating'), 2) : null;

        $ratingDistribution = collect(range(1, 5))->mapWithKeys(fn ($star) => [
            $star => $ratingQuery()->where('overall_rating', $star)->count(),
        ]);

        $byService = $baseQuery()
            ->with('serviceType')
            ->get()
            ->groupBy('service_type_id')
            ->map(function ($tickets) {
                $serviceType = $tickets->first()->serviceType;
                $ticketIds = $tickets->pluck('id');
                $ratings = TicketRating::whereIn('ticket_id', $ticketIds)->get();

                return [
                    'name' => $serviceType?->name ?? 'Unknown',
                    'total' => $tickets->count(),
                    'resolved' => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
                    'avg_overall' => $ratings->count() ? round($ratings->avg('overall_rating'), 2) : null,
                    'avg_service' => $ratings->count() ? round($ratings->avg('service_rating'), 2) : null,
                    'rated_count' => $ratings->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $byStaff = $baseQuery()
            ->whereNotNull('assigned_to')
            ->with('assignee')
            ->get()
            ->groupBy('assigned_to')
            ->map(function ($tickets) {
                $assignee = $tickets->first()->assignee;
                $ticketIds = $tickets->pluck('id');
                $ratings = TicketRating::whereIn('ticket_id', $ticketIds)->get();

                return [
                    'name' => $assignee?->name ?? 'Unknown',
                    'handled' => $tickets->count(),
                    'resolved' => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
                    'avg_overall' => $ratings->count() ? round($ratings->avg('overall_rating'), 2) : null,
                    'avg_staff' => $ratings->whereNotNull('staff_rating')->count()
                        ? round($ratings->whereNotNull('staff_rating')->avg('staff_rating'), 2) : null,
                    'rated_count' => $ratings->count(),
                ];
            })
            ->sortByDesc('handled')
            ->values();

        return compact(
            'office', 'from', 'to',
            'totalTickets', 'resolvedTickets', 'pendingTickets', 'inProgressTickets', 'cancelledTickets',
            'avgResolutionHours', 'totalRatings', 'avgOverall', 'avgService', 'avgStaff',
            'ratingDistribution', 'byService', 'byStaff',
        );
    }

    public function getPrintUrl(): string
    {
        return route('admin.reports.print', [
            'office' => $this->officeId,
            'from' => $this->dateFrom,
            'to' => $this->dateTo,
        ]);
    }
}
