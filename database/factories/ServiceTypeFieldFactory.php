<?php

namespace Database\Factories;

use App\Enums\FieldType;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceTypeField>
 */
class ServiceTypeFieldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_type_id' => ServiceType::factory(),
            'label' => ucwords(fake()->words(3, true)),
            'field_type' => fake()->randomElement(FieldType::cases()),
            'options' => null,
            'is_required' => fake()->boolean(),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
