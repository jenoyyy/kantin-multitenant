<?php

namespace App\Modules\Admin\Services;

use App\Models\Canteen;
use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Models\TenantBalance;
use Illuminate\Support\Facades\DB;

/**
 * Membuat tenant + komisi aktif awal + saldo nol dalam satu transaksi.
 * Kalau salah satu gagal, semuanya dibatalkan.
 */
final class CreateTenant
{
    public function __construct(private AuditLogger $audit) {}

    /** @param array<string, mixed> $data */
    public function handle(Canteen $canteen, array $data): Tenant
    {
        return DB::transaction(function () use ($canteen, $data): Tenant {
            $tenant = (new Tenant)->forceFill([
                'canteen_id' => $canteen->id,
                'code' => $data['code'],
                'slug' => $data['slug'],
                'display_name' => $data['display_name'],
                'status' => 'active',
            ]);
            $tenant->save();

            (new TenantBalance)->forceFill([
                'tenant_id' => $tenant->id,
                'amount' => 0,
            ])->save();

            (new CommissionScheme)->forceFill([
                'tenant_id' => $tenant->id,
                'commission_rate' => $data['commission_rate'],
                'valid_from' => now(),
                'valid_to' => null,
            ])->save();

            $this->audit->record('tenant.created', $tenant, [
                'after' => $tenant->only(['code', 'slug', 'display_name', 'status']),
            ]);

            return $tenant;
        });
    }
}