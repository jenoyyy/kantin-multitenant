<?php

namespace Database\Seeders;

use App\Models\OperatingHour;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DemoOperatingHoursSeeder extends Seeder
{
    public function run(): void
    {
        $perKantin = Tenant::query()->where('status', 'active')->orderBy('id')->get()->groupBy('canteen_id');

        foreach ($perKantin as $tenants) {
            foreach ($tenants->values() as $i => $tenant) {
                [$opens, $closes] = $i === 0 ? ['10:00:00', '21:00:00'] : ['00:00:00', '23:59:00'];
                foreach (range(0, 6) as $day) {
                    OperatingHour::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'day_of_week' => $day],
                        ['open_time' => $opens, 'close_time' => $closes, 'is_closed' => false],
                    );
                }
            }
        }
    }
}
