<?php

namespace Database\Seeders;

use App\Models\Canteen;
use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Models\TenantBalance;
use App\Models\User;
use App\Models\UserCanteenRole;
use Illuminate\Database\Seeder;

/**
 * Data uji Modul 5: akun pengelola + keanggotaan kantin.
 * Aman dijalankan berulang (idempotent).
 */
class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $canteen = Canteen::query()->firstOrFail();

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@kantin.test'],
            [
                'name' => 'Pengelola Kantin',
                'password' => 'password',
                'role' => 'admin',
                'status' => 'active',
            ],
        );
        $admin->forceFill(['email_verified_at' => now()])->save();

        UserCanteenRole::query()->updateOrCreate(
            ['user_id' => $admin->id, 'canteen_id' => $canteen->id],
            ['role' => 'owner'],
        );

        // Lengkapi tenant lama: saldo awal dan komisi aktif (10%) bila belum ada.
        foreach (Tenant::query()->where('canteen_id', $canteen->id)->get() as $tenant) {
            TenantBalance::query()->firstOrCreate(
                ['tenant_id' => $tenant->id],
                ['amount' => 0],
            );

            if (! CommissionScheme::query()->where('tenant_id', $tenant->id)->exists()) {
                CommissionScheme::query()->create([
                    'tenant_id' => $tenant->id,
                    'commission_rate' => 0.10,
                    'valid_from' => now(),
                    'valid_to' => null,
                ]);
            }
        }
    }
}