<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->pluck('name')->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $roles = $this->data['roles'] ?? [];
        $this->record->syncRoles($roles);

        // Prevent a super_admin from removing their own super_admin role
        if ($this->record->is(auth()->user()) && ! $this->record->hasRole('super_admin')) {
            $this->record->assignRole('super_admin');
        }
    }
}
