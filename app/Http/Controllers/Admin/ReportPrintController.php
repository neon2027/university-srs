<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketRating;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportPrintController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'office' => 'required|exists:offices,id',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $user = auth()->user();
        $officeId = (int) $validated['office'];

        if (! $user->hasRole('super_admin')) {
            abort_unless($user->offices()->where('offices.id', $officeId)->exists(), 403);
        }

        $office = Office::findOrFail($officeId);
        $from = $validated['from'];
        $to = $validated['to'];

        $baseQuery = fn () => Ticket::where('office_id', $officeId)
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
            ->where('office_id', $officeId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']));

        $totalRatings = $ratingQuery()->count();
        $avgOverall = $totalRatings ? round($ratingQuery()->avg('overall_rating'), 2) : null;
        $avgService = $totalRatings ? round($ratingQuery()->avg('service_rating'), 2) : null;
        $avgStaff = $totalRatings
            ? round($ratingQuery()->whereNotNull('staff_rating')->avg('staff_rating'), 2)
            : null;

        $ratingDistribution = collect(range(1, 5))->mapWithKeys(
            fn ($star) => [$star => $ratingQuery()->where('overall_rating', $star)->count()]
        );

        $byService = $this->buildServiceBreakdown($baseQuery);
        $byStaff = $this->buildStaffBreakdown($baseQuery);

        $resolutionRate = $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 0;
        $responseRate = $totalTickets > 0 ? round(($totalRatings / $totalTickets) * 100, 1) : 0;
        $avgHoursLabel = $avgResolutionHours !== null
            ? ($avgResolutionHours >= 24
                ? round($avgResolutionHours / 24, 1).' days'
                : round($avgResolutionHours, 1).' hrs')
            : '—';

        return view('reports.print', compact(
            'office', 'from', 'to',
            'totalTickets', 'resolvedTickets', 'pendingTickets', 'inProgressTickets', 'cancelledTickets',
            'avgHoursLabel', 'resolutionRate', 'responseRate',
            'totalRatings', 'avgOverall', 'avgService', 'avgStaff',
            'ratingDistribution', 'byService', 'byStaff',
        ));
    }

    private function buildServiceBreakdown(callable $baseQuery): Collection
    {
        return $baseQuery()
            ->with('serviceType')
            ->get()
            ->groupBy('service_type_id')
            ->map(function ($tickets) {
                $ticketIds = $tickets->pluck('id');
                $ratings = TicketRating::whereIn('ticket_id', $ticketIds)->get();

                return [
                    'name' => $tickets->first()->serviceType?->name ?? 'Unknown',
                    'total' => $tickets->count(),
                    'resolved' => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
                    'avg_overall' => $ratings->count() ? round($ratings->avg('overall_rating'), 2) : null,
                    'avg_service' => $ratings->count() ? round($ratings->avg('service_rating'), 2) : null,
                    'rated_count' => $ratings->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function buildStaffBreakdown(callable $baseQuery): Collection
    {
        return $baseQuery()
            ->whereNotNull('assigned_to')
            ->with('assignee')
            ->get()
            ->groupBy('assigned_to')
            ->map(function ($tickets) {
                $ticketIds = $tickets->pluck('id');
                $ratings = TicketRating::whereIn('ticket_id', $ticketIds)->get();

                return [
                    'name' => $tickets->first()->assignee?->name ?? 'Unknown',
                    'handled' => $tickets->count(),
                    'resolved' => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
                    'avg_overall' => $ratings->count() ? round($ratings->avg('overall_rating'), 2) : null,
                    'avg_staff' => $ratings->whereNotNull('staff_rating')->count()
                        ? round($ratings->whereNotNull('staff_rating')->avg('staff_rating'), 2)
                        : null,
                    'rated_count' => $ratings->count(),
                ];
            })
            ->sortByDesc('handled')
            ->values();
    }
}
