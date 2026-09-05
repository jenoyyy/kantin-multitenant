<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
  public function definition(): array
{
    $tenant = \App\Models\Tenant::factory()->create();
    $category = \App\Models\MenuCategory::factory()->for($tenant)->create();

    return [
        'tenant_id' => $tenant->id,
        'menu_category_id' => $category->id,
        'name' => $this->faker->words(2, true),
        'price_amount' => $this->faker->numberBetween(5000, 50000),
        'is_available' => true,
    ];
}
}
