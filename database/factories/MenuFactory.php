<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Tenant;
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
        return [
            'tenant_id' => Tenant::factory(),
            'menu_category_id' => MenuCategory::factory(),
            'name' => $this->faker->words(2, true),
            'price_amount' => $this->faker->numberBetween(5000, 50000),
            'is_available' => true,
        ];
    }
}
