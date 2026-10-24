<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Canteen;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserCanteenRole;
use App\Modules\Admin\Http\Requests\StoreTenantRequest;
use App\Modules\Admin\Services\CreateTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminTenantController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CreateTenant $createTenant) {}

    /** Kantin yang dikelola user ini (diambil dari keanggotaan, bukan dari input). */
    private function canteen(Request $request): Canteen
    {
        $id = UserCanteenRole::query()
            ->where('user_id', $request->user()?->id)
            ->whereIn('role', ['owner', 'manager', 'finance'])
            ->value('canteen_id');
        abort_if($id === null, 403, 'Anda tidak mengelola kantin mana pun.');

        return Canteen::query()->findOrFail((int) $id);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Tenant::class);
        $canteen = $this->canteen($request);
        $tenants = Tenant::query()
            ->where('canteen_id', $canteen->id)
            ->with([
                'tenantRoles' => fn ($q) => $q->where('role', 'owner')->with('user'),
                'bankAccounts' => fn ($q) => $q->where('is_primary', true),
                'commissionSchemes' => fn ($q) => $q->effectiveAt(now()),
            ])
            ->orderBy('display_name')
            ->get();

        return view('admin.tenants.index', compact('canteen', 'tenants'));
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        return view('admin.tenants.create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $canteen = $this->canteen($request);
        $data = $request->validated();

        $tenant = $this->createTenant->handle($canteen, [
            'code' => $data['code'],
            'slug' => $data['slug'],
            'display_name' => $data['display_name'],
            'commission_rate' => (float) $data['commission_rate'] / 100, // persen -> pecahan
            'pic_name' => $data['pic_name'],
            'pic_email' => $data['pic_email'],
            'bank_name' => $data['bank_name'],
            'account_holder_name' => $data['account_holder_name'],
            'account_number' => $data['account_number'],
        ]);

        return redirect()
            ->route('admin.tenants.edit', $tenant)
            ->with('status', "Tenant dibuat. Tautan atur kata sandi dikirim ke {$data['pic_email']}.");
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);
        $tenant->load('balance');
        $commissions = $tenant->commissionSchemes()->orderByDesc('valid_from')->get();
        $members = $tenant->tenantRoles()->with('user')->get();
        $accounts = $tenant->bankAccounts()->orderByDesc('is_primary')->orderBy('id')->get();

        // UC-21 langkah 6: siapa pengelola yang mengubah komisi (dari audit_logs).
        $commissionLogs = AuditLog::query()
            ->where('target_type', 'Tenant')
            ->where('target_id', $tenant->id)
            ->where('action', 'like', '%commission%')
            ->latest('id')
            ->get();
        $actorNames = User::query()
            ->whereIn('id', $commissionLogs->pluck('actor_id')->filter()->unique())
            ->pluck('name', 'id');

        return view('admin.tenants.edit', compact(
            'tenant', 'commissions', 'members', 'accounts', 'commissionLogs', 'actorNames'
        ));
    }
}
