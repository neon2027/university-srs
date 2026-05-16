<?php

namespace App\Filament\Resources\OfficeResource\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('is_primary')
                ->label('Primary office for this staff member'),
        ]);
    }

    public function table(Table $table): Table
    {
        $staffRoles = Role::whereIn('name', ['staff', 'office_admin'])
            ->pluck('name', 'name');

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'office_admin' => 'warning',
                        'staff' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('pivot.is_primary')
                    ->label('Primary')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->form(fn (AttachAction $action) => [
                        $action->getRecordSelect(),
                        Select::make('role')
                            ->label('Role')
                            ->options($staffRoles)
                            ->required(),
                        Toggle::make('is_primary')->label('Primary office'),
                    ])
                    ->preloadRecordSelect()
                    ->after(function (array $data, User $record): void {
                        if (! empty($data['role'])) {
                            $record->syncRoles([$data['role']]);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('change_role')
                    ->label('Change Role')
                    ->icon('heroicon-o-user-circle')
                    ->color('warning')
                    ->form([
                        Select::make('role')
                            ->label('Role')
                            ->options($staffRoles)
                            ->default(fn (User $record) => $record->roles->whereIn('name', ['staff', 'office_admin'])->first()?->name)
                            ->required(),
                    ])
                    ->action(fn (array $data, User $record) => $record->syncRoles([$data['role']])),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
