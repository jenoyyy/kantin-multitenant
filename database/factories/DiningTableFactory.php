<?php

namespace Database\Factories;

use App\Models\Canteen;
use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiningTable>
 */
class DiningTableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'canteen_id' => Canteen::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('M-????')),
            'label' => 'Meja '.$this->faker->unique()->numberBetween(1, 99999),
            'zone' => null,
            'status' => 'active',
        ];
    }
}
