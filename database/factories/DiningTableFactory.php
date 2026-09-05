<?php

namespace Database\Factories;

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
        'canteen_id' => \App\Models\Canteen::factory(),
        'label' => strtoupper($this->faker->unique()->lexify('T-??')),
        'status' => 'available',
    ];
}
}
