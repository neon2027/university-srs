<?php

namespace App\Filament\Actions;

use App\Enums\EventType;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Filament\Actions\Action;
use Filament\Forms;
use Illuminate\Support\Facades\DB;

class ChangeStatusAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'change_status';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Change Status')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->form([
                Forms\Components\Select::make('status')
                    ->label('New Status')
                    ->options(
                        collect(TicketStatus::cases())
                            ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                    )
                    ->default(fn (Ticket $record) => $record->status->value)
                    ->required(),
                Forms\Components\Textarea::make('note')
                    ->label('Note (optional)')
                    ->placeholder('Reason for status change...')
                    ->rows(2)
                    ->maxLength(500),
            ])
            ->action(function (Ticket $record, array $data): void {
                $newStatus = TicketStatus::from($data['status']);

                if ($newStatus === $record->status) {
                    return;
                }

                DB::transaction(function () use ($record, $newStatus, $data): void {
                    $previousStatus = $record->status;

                    $updates = ['status' => $newStatus];

                    if ($newStatus === TicketStatus::Resolved && $record->resolved_at === null) {
                        $updates['resolved_at'] = now();
                    }

                    if ($newStatus === TicketStatus::Closed && $record->closed_at === null) {
                        $updates['closed_at'] = now();
                    }

                    if (! in_array($newStatus, [TicketStatus::Resolved, TicketStatus::Closed])) {
                        $updates['resolved_at'] = null;
                        $updates['closed_at'] = null;
                    }

                    $record->update($updates);

                    $eventType = match ($newStatus) {
                        TicketStatus::Resolved => EventType::Resolved,
                        TicketStatus::Closed => EventType::Closed,
                        default => EventType::StatusChanged,
                    };

                    TicketHistory::create([
                        'ticket_id' => $record->id,
                        'actor_id' => auth()->id(),
                        'event_type' => $eventType,
                        'from_status' => $previousStatus,
                        'to_status' => $newStatus,
                        'note' => $data['note'] ?: null,
                    ]);
                });
            })
            ->successNotificationTitle('Status updated');
    }
}
