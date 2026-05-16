<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@ibuconnect.edu.ph',
            ],
        ];

        foreach ($admins as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name']],
            );

            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->syncRoles(['super_admin']);

            $this->command->info("Super admin ready: {$user->email} (log in via Google OAuth)");
        }
    }
}
