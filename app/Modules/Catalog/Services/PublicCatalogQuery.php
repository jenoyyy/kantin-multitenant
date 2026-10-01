<?php

namespace App\Modules\Catalog\Services;

use App\Models\Canteen;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;

/**
 * Satu-satunya tempat yang boleh melewati global scope 'tenant'.
 *
 * Alasan bypass: katalog pelanggan menampilkan menu dari banyak tenant
 * dalam satu kantin, sedangkan scope biasa hanya mengizinkan satu tenant.
 *
 * Filter pengganti (wajib): tenant milik kantin yang dipilih (hasil QR),
 * tenant berstatus aktif, dan menu yang tersedia.
 */
final class PublicCatalogQuery
{
    /**
     * @return Collection<int, Menu>
     */
    public function menusForCanteen(Canteen $canteen): Collection
    {
        return Menu::query()
            ->withoutGlobalScope('tenant')
            ->whereHas('tenant', function ($query) use ($canteen): void {
                $query->where('canteen_id', $canteen->id)
                    ->where('status', 'active');
            })
            ->where('is_available', true)
            ->orderBy('id')
            ->get(['id', 'tenant_id', 'menu_category_id', 'name', 'price_amount']);
    }
}
