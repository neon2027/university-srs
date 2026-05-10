<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Actions\AssignTicketAction;
use App\Filament\Resources\TicketResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AssignTicketAction::make(),
            EditAction::make(),
        ];
    }
}
