<?php

namespace App\Filament\Actions;

use App\Enums\EventType;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Illuminate\Support\Facades\DB;

class AssignTicketAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'assign_ticket';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Assign')
            ->icon('heroicon-o-user-plus')
            ->color('info')
            ->form([
                Forms\Components\Select::make('assignee_id')
                    ->label('Assign to')
                    ->options(function () {
                        $user = auth()->user();

                        if ($user->hasRole('super_admin')) {
                            return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'office_admin']))
                                ->pluck('name', 'id');
                        }

                        return $user->offices->flatMap->staff->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
            ])
            ->action(function (Ticket $record, array $data): void {
                DB::transaction(function () use ($record, $data): void {
                    $previousStatus = $record->status;

                    $record->update([
                        'assigned_to' => $data['assignee_id'],
                        'status' => TicketStatus::Assigned,
                    ]);

                    TicketHistory::create([
                        'ticket_id' => $record->id,
                        'actor_id' => auth()->id(),
                        'event_type' => EventType::Assigned,
                        'from_status' => $previousStatus,
                        'to_status' => TicketStatus::Assigned,
                        'note' => null,
                        'meta' => ['assignee_id' => $data['assignee_id']],
                    ]);
                });
            })
            ->successNotificationTitle('Ticket assigned');
    }
}
