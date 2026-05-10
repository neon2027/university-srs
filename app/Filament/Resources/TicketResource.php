<?php

namespace App\Filament\Resources;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['office', 'serviceType', 'requester', 'assignee']);

        $user = auth()->user();

        if ($user->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = $user->offices()->pluck('offices.id');
            $query->whereIn('office_id', $officeIds);
        }

        return $query;
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
                    ->color(fn (TicketStatus $state) => match ($state) {
                        TicketStatus::Pending => 'warning',
                        TicketStatus::Assigned, TicketStatus::InProgress => 'info',
                        TicketStatus::Forwarded => 'primary',
                        TicketStatus::Resolved, TicketStatus::Closed => 'success',
                        TicketStatus::OnHold => 'gray',
                        TicketStatus::Cancelled => 'danger',
                    })
                    ->formatStateUsing(fn (TicketStatus $state) => $state->label()),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (TicketPriority $state) => match ($state) {
                        TicketPriority::Urgent => 'danger',
                        TicketPriority::High => 'warning',
                        TicketPriority::Normal => 'info',
                        TicketPriority::Low => 'gray',
                    })
                    ->formatStateUsing(fn (TicketPriority $state) => ucfirst($state->value)),
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
                    ->options(collect(TicketStatus::cases())->mapWithKeys(
                        fn (TicketStatus $s) => [$s->value => $s->label()]
                    )),
                SelectFilter::make('priority')
                    ->options(collect(TicketPriority::cases())->mapWithKeys(
                        fn (TicketPriority $p) => [$p->value => ucfirst($p->value)]
                    )),
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
                    ->options(collect(TicketStatus::cases())->mapWithKeys(
                        fn (TicketStatus $s) => [$s->value => $s->label()]
                    ))
                    ->required(),
                Select::make('priority')
                    ->options(collect(TicketPriority::cases())->mapWithKeys(
                        fn (TicketPriority $p) => [$p->value => ucfirst($p->value)]
                    ))
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
                Section::make('Ticket Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('ulid')
                            ->label('Ticket ID')
                            ->fontFamily('mono')
                            ->copyable(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (TicketStatus $state) => match ($state) {
                                TicketStatus::Pending => 'warning',
                                TicketStatus::Assigned, TicketStatus::InProgress => 'info',
                                TicketStatus::Forwarded => 'primary',
                                TicketStatus::Resolved, TicketStatus::Closed => 'success',
                                TicketStatus::OnHold => 'gray',
                                TicketStatus::Cancelled => 'danger',
                            })
                            ->formatStateUsing(fn (TicketStatus $state) => $state->label()),
                        TextEntry::make('requester.name')
                            ->label('Requester'),
                        TextEntry::make('office.name')
                            ->label('Office'),
                        TextEntry::make('serviceType.name')
                            ->label('Service Type'),
                        TextEntry::make('priority')
                            ->formatStateUsing(fn (TicketPriority $state) => ucfirst($state->value))
                            ->badge()
                            ->color(fn (TicketPriority $state) => match ($state) {
                                TicketPriority::Urgent => 'danger',
                                TicketPriority::High => 'warning',
                                TicketPriority::Normal => 'info',
                                TicketPriority::Low => 'gray',
                            }),
                        TextEntry::make('assignee.name')
                            ->label('Assigned To')
                            ->default('Unassigned'),
                        TextEntry::make('created_at')
                            ->label('Submitted')
                            ->since(),
                        TextEntry::make('resolved_at')
                            ->label('Resolved')
                            ->since()
                            ->default('Not yet resolved'),
                    ]),
            ]);
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
