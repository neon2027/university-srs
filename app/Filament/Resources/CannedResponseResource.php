<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CannedResponseResource\Pages;
use App\Models\CannedResponse;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CannedResponseResource extends Resource
{
    protected static ?string $model = CannedResponse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'office_admin']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user?->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = $user->offices()->pluck('offices.id');
            $query->where(fn (Builder $q) => $q->whereIn('office_id', $officeIds)->orWhereNull('office_id'));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('office_id')
                ->relationship('office', 'name')
                ->nullable()
                ->searchable()
                ->preload()
                ->helperText('Leave empty to make this a global template.'),
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Textarea::make('body')
                ->required()
                ->rows(4)
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('office.name')
                    ->badge()
                    ->default('Global')
                    ->label('Office'),
                TextColumn::make('body')
                    ->limit(60)
                    ->label('Body'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCannedResponses::route('/'),
            'create' => Pages\CreateCannedResponse::route('/create'),
            'edit' => Pages\EditCannedResponse::route('/{record}/edit'),
        ];
    }
}
