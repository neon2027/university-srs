<?php

namespace App\Filament\Resources\OfficeResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                IconColumn::make('pivot.is_primary')
                    ->label('Primary')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->form(fn (AttachAction $action) => [
                        $action->getRecordSelect(),
                        Toggle::make('is_primary')->label('Primary office'),
                    ])
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
