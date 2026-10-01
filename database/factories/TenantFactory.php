<?php

namespace Database\Factories;

use App\Models\Canteen;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'canteen_id' => Canteen::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('TNT-???')),
            'slug' => Str::slug($name),
            'display_name' => $name,
            'status' => 'active',
        ];
    }
}
