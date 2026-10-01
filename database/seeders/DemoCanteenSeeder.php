<?php

namespace Database\Seeders;

use App\Models\Canteen;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DemoCanteenSeeder extends Seeder
{
    public function run(): void
    {
        $canteen = Canteen::factory()->create([
            'code' => 'DEMO-01',
            'name' => 'Kantin Demo Kampus',
        ]);

        DiningTable::factory()->count(5)->for($canteen)->create();

        $tenants = Tenant::factory()
            ->count(2)
            ->for($canteen)
            ->create();

        foreach ($tenants as $tenant) {
            $category = MenuCategory::factory()->for($tenant)->create([
                'name' => 'Makanan Utama',
            ]);

            Menu::factory()
                ->count(3)
                ->for($tenant)
                ->for($category, 'category')
                ->create();
        }
    }
}
