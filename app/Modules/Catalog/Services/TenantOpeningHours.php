<?php

namespace App\Modules\Catalog\Services;

use App\Models\Canteen;
use App\Models\OperatingHour;
use App\Models\Tenant;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Status buka/tutup tenant dan kantin berdasarkan `operating_hours` pada waktu lokal
 * (config app.display_timezone, default Asia/Jakarta). Dipakai UC-02 (alur 3b, kantin tutup)
 * dan halaman sambutan sesudah pindai QR. Tenant tanpa jam terkonfigurasi dianggap buka.
 */
final class TenantOpeningHours
{
    public function isOpen(Tenant $tenant, ?CarbonInterface $at = null): bool
    {
        $hours = $this->hoursFor($tenant);
        if ($hours->isEmpty()) {
            return true;
        }

        $local = $this->local($at);
        $today = $this->todayRow($hours, $local);
        if ($today === null) {
            return false;
        }

        $now = $local->format('H:i:s');
        $opens = (string) $today->open_time;
        $closes = (string) $today->close_time;

        // Jam melewati tengah malam (mis. 18:00-02:00).
        return $opens <= $closes ? ($now >= $opens && $now < $closes) : ($now >= $opens || $now < $closes);
    }

    /** Label jam hari ini, mis. "10.00–21.00", atau null bila tidak dikonfigurasi/libur. */
    public function todayLabel(Tenant $tenant, ?CarbonInterface $at = null): ?string
    {
        $today = $this->todayRow($this->hoursFor($tenant), $this->local($at));

        return $today === null ? null : $this->fmt((string) $today->open_time).'–'.$this->fmt((string) $today->close_time);
    }

    /** Jam buka hari ini bila tenant belum buka, mis. "10.00". */
    public function opensAtToday(Tenant $tenant, ?CarbonInterface $at = null): ?string
    {
        $today = $this->todayRow($this->hoursFor($tenant), $this->local($at));

        return $today === null ? null : $this->fmt((string) $today->open_time);
    }

    /**
     * Kantin buka bila sedikitnya satu tenant aktif sedang buka. Bila belum ada tenant aktif yang
     * mengatur jam operasional, kantin dianggap buka (jam belum dikonfigurasi, bukan tutup).
     */
    public function canteenIsOpen(Canteen $canteen, ?CarbonInterface $at = null): bool
    {
        $tenants = $this->activeTenants($canteen);
        if ($tenants->every(fn (Tenant $tenant): bool => $tenant->operatingHours->isEmpty())) {
            return true;
        }

        return $tenants->contains(fn (Tenant $tenant): bool => $this->isOpen($tenant, $at));
    }

    /** @return Collection<int, Tenant> */
    public function activeTenants(Canteen $canteen): Collection
    {
        return Tenant::query()
            ->where('canteen_id', $canteen->id)
            ->where('status', 'active')
            ->with('operatingHours')
            ->orderBy('display_name')
            ->get();
    }

    /** @return Collection<int, OperatingHour> */
    private function hoursFor(Tenant $tenant): Collection
    {
        return $tenant->relationLoaded('operatingHours')
            ? $tenant->operatingHours
            : $tenant->operatingHours()->get();
    }

    /** Baris jam untuk hari lokal ini; null bila tidak ada barisnya atau hari itu libur (is_closed). */
    private function todayRow(Collection $hours, CarbonInterface $local): ?OperatingHour
    {
        $row = $hours->firstWhere('day_of_week', $local->dayOfWeek);

        return $row === null || $row->is_closed ? null : $row;
    }

    private function local(?CarbonInterface $at): CarbonInterface
    {
        return ($at ?? now())->copy()->setTimezone((string) config('app.display_timezone', 'Asia/Jakarta'));
    }

    private function fmt(string $time): string
    {
        return str_replace(':', '.', substr($time, 0, 5));
    }
}
