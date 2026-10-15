<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionScheme;
use App\Models\Tenant;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Komisi bertanggal efektif: kunci versi aktif, tolak overlap, tutup versi lama
 * TEPAT pada waktu efektif baru (interval setengah-terbuka), lalu sisipkan versi baru.
 * Guard DB (uq_commission_active_per_tenant) menjadi lapis terakhir.
 */
final class ChangeCommissionSchedule
{
    public function __construct(private AuditLogger $audit) {}

    public function handle(Tenant $tenant, int|float|string $rate, CarbonInterface $effectiveAt): CommissionScheme
    {
        return DB::transaction(function () use ($tenant, $rate, $effectiveAt): CommissionScheme {
            $active = CommissionScheme::query()
                ->where('tenant_id', $tenant->id)
                ->whereNull('valid_to')
                ->lockForUpdate()
                ->first();

            if ($active !== null && $active->valid_from >= $effectiveAt) {
                throw new DomainException('Waktu efektif harus setelah versi aktif (mencegah tumpang tindih).');
            }

            $before = $active?->only(['id', 'commission_rate', 'valid_from', 'valid_to']);

            // Versi lama berakhir tepat saat versi baru mulai: tidak ada celah, tidak ada overlap.
            $active?->forceFill(['valid_to' => $effectiveAt])->save();

            $new = (new CommissionScheme)->forceFill([
                'tenant_id' => $tenant->id,
                'commission_rate' => $rate,
                'valid_from' => $effectiveAt,
                'valid_to' => null,
            ]);
            $new->save();

            $this->audit->record('tenant.commission_scheduled', $tenant, [
                'before' => $before,
                'after' => $new->only(['id', 'commission_rate', 'valid_from']),
            ]);

            return $new;
        });
    }
}