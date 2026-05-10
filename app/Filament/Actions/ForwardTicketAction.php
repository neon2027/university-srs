<?php

namespace App\Filament\Actions;

use App\Enums\CreditType;
use App\Enums\EventType;
use App\Enums\TicketStatus;
use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Filament\Actions\Action;
use Filament\Forms;
use Illuminate\Support\Facades\DB;

class ForwardTicketAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'forward_ticket';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Forward')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('warning')
            ->form([
                Forms\Components\Select::make('to_office_id')
                    ->label('Forward to Office')
                    ->options(fn (Ticket $record) => Office::where('is_active', true)
                        ->where('id', '!=', $record->office_id)
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('credit_type')
                    ->label('Credit Attribution')
                    ->options([
                        CreditType::AcceptCredit->value => 'Accept Credit — both offices receive credit',
                        CreditType::ReferenceOnly->value => 'Reference Only — credit stays with originating office',
                    ])
                    ->required()
                    ->helperText('Determines how this ticket contributes to performance metrics.'),
                Forms\Components\Textarea::make('note')
                    ->label('Forwarding Note')
                    ->placeholder('Reason for forwarding...')
                    ->rows(3),
            ])
            ->action(function (Ticket $record, array $data): void {
                DB::transaction(function () use ($record, $data): void {
                    $fromOfficeId = $record->office_id;
                    $previousStatus = $record->status;

                    ForwardingLog::create([
                        'ticket_id' => $record->id,
                        'from_office_id' => $fromOfficeId,
                        'to_office_id' => $data['to_office_id'],
                        'forwarded_by' => auth()->id(),
                        'credit_type' => $data['credit_type'],
                        'note' => $data['note'] ?? null,
                        'forwarded_at' => now(),
                    ]);

                    $record->update([
                        'office_id' => $data['to_office_id'],
                        'status' => TicketStatus::Forwarded,
                        'assigned_to' => null,
                    ]);

                    TicketHistory::create([
                        'ticket_id' => $record->id,
                        'actor_id' => auth()->id(),
                        'event_type' => EventType::Forwarded,
                        'from_status' => $previousStatus,
                        'to_status' => TicketStatus::Forwarded,
                        'meta' => [
                            'from_office_id' => $fromOfficeId,
                            'to_office_id' => $data['to_office_id'],
                            'credit_type' => $data['credit_type'],
                        ],
                    ]);
                });
            })
            ->successNotificationTitle('Ticket forwarded');
    }
}
