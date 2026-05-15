<?php

namespace App\Filament\Resources;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\Actions\AssignTicketAction;
use App\Filament\Actions\ForwardTicketAction;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Tickets';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordRouteKeyName = 'ulid';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['office', 'serviceType', 'requester', 'assignee']);

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        if ($user->hasAnyRole(['staff', 'office_admin'])) {
            return $query->whereIn('office_id', $user->offices()->pluck('offices.id'));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('ulid')
                    ->label('Ticket ID')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('requester.name')
                    ->label('Requester')
                    ->searchable(),
                TextColumn::make('office.name')
                    ->label('Office')
                    ->badge()
                    ->searchable(),
                TextColumn::make('serviceType.name')
                    ->label('Service')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TicketStatus $state) => $state->color())
                    ->formatStateUsing(fn (TicketStatus $state) => $state->label()),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (TicketPriority $state) => $state->color())
                    ->formatStateUsing(fn (TicketPriority $state) => $state->label()),
                TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->default('—')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(TicketStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                SelectFilter::make('priority')
                    ->options(collect(TicketPriority::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()])),
                SelectFilter::make('office')
                    ->relationship('office', 'name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
                Filter::make('unassigned')
                    ->label('Unassigned only')
                    ->query(fn (Builder $q) => $q->whereNull('assigned_to'))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
                AssignTicketAction::make(),
                ForwardTicketAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(collect(TicketStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->required(),
                Select::make('priority')
                    ->options(collect(TicketPriority::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()]))
                    ->required(),
                Select::make('assigned_to')
                    ->label('Assignee')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                ViewEntry::make('ticket_workspace')
                    ->label('')
                    ->view('filament.resources.ticket-resource.ticket-workspace'),
            ])->columns(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'view' => Pages\ViewTicket::route('/{record:ulid}'),
            'edit' => Pages\EditTicket::route('/{record:ulid}/edit'),
        ];
    }
}
