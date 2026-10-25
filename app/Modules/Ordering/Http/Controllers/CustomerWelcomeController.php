<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Canteen;
use App\Modules\Catalog\Services\TenantOpeningHours;
use App\Modules\Ordering\Services\ResolveCustomerSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * UC-02 langkah 5: sambutan sesudah pindai QR. Meja/zona dibaca dari sesi anonim (bukan input klien),
 * plus daftar tenant aktif beserta status buka/tutup. Tanpa sesi yang cocok, pelanggan diminta
 * memindai QR meja.
 */
class CustomerWelcomeController extends Controller
{
    public function __construct(private ResolveCustomerSession $sessions, private TenantOpeningHours $hours) {}

    public function __invoke(Request $request, string $canteen): View
    {
        $canteenModel = Canteen::query()->where('code', $canteen)->where('status', 'active')->firstOrFail();
        $session = $this->sessions->current($request);
        $table = $session !== null && $session->canteen_id === $canteenModel->id ? $session->diningTable : null;

        return view('customer.welcome', [
            'canteen' => $canteenModel,
            'table' => $table,
            'tenants' => $this->hours->activeTenants($canteenModel),
            'hours' => $this->hours,
        ]);
    }
}
