<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(4, true));

        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => $name,
            'description' => fake()->sentence(),
            'sla_days' => fake()->optional()->numberBetween(1, 30),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
