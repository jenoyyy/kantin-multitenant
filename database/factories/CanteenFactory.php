<?php

namespace Database\Factories;

use App\Models\Canteen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Canteen>
 */
class CanteenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
  public function definition(): array
{
    return [
        'code' => strtoupper($this->faker->unique()->lexify('CNT-???')),
        'name' => $this->faker->company() . ' Canteen',
        'status' => 'active',
    ];
}
}
