<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            ['name' => 'Information Technology Office', 'email' => 'ito@bicol-u.edu.ph'],
            ['name' => 'Physical Plant Office', 'email' => 'ppo@bicol-u.edu.ph'],
            ['name' => 'Registrar Office', 'email' => 'registrar@bicol-u.edu.ph'],
            ['name' => 'Student Affairs Office', 'email' => 'sao@bicol-u.edu.ph'],
            ['name' => 'Finance Office', 'email' => 'finance@bicol-u.edu.ph'],
        ];

        foreach ($offices as $data) {
            Office::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
