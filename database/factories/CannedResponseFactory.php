<?php

namespace Database\Factories;

use App\Models\CannedResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CannedResponse>
 */
class CannedResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'office_id' => null,
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'created_by' => User::factory(),
            'is_active' => true,
        ];
    }
}
