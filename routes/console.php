<?php

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:verify-employee {email : User email address} {--office= : Office ID, slug, or name. Defaults to the pending office.} {--role=staff : Role to assign after approval.}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("User [{$email}] was not found.");

        return 1;
    }

    $officeOption = $this->option('office');
    $office = $officeOption
        ? Office::query()
            ->when(is_numeric($officeOption), fn ($query) => $query->whereKey($officeOption))
            ->when(! is_numeric($officeOption), fn ($query) => $query
                ->where('slug', $officeOption)
                ->orWhere('name', $officeOption))
            ->first()
        : $user->pendingOffice;

    if (! $office) {
        $this->error('No office was found. Pass --office= with an office ID, slug, or exact name.');

        return 1;
    }

    $role = $this->option('role') ?: 'staff';

    if (! Role::where('name', $role)->where('guard_name', 'web')->exists()) {
        $this->error("Role [{$role}] was not found.");

        return 1;
    }

    DB::transaction(function () use ($user, $office, $role): void {
        $user->offices()->syncWithoutDetaching([
            $office->id => ['is_primary' => ! $user->offices()->wherePivot('is_primary', true)->exists()],
        ]);

        $user->assignRole($role);

        $user->update([
            'onboarding_status' => null,
            'pending_office_id' => null,
            'onboarding_completed_at' => now(),
        ]);
    });

    $this->info("Verified {$user->email} as {$role} for {$office->name}.");

    return 0;
})->purpose('Approve a pending employee and attach them to an office.');
