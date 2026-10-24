<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\AssignTenantRole;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminTenantRoleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private AssignTenantRole $service) {}

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('assignRole', $tenant);

        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'in:owner,staff,cashier'],
        ]);

        $user = User::query()->where('email', $data['email'])->firstOrFail();

        try {
            $this->service->assign($tenant, $user, $data['role']);
        } catch (DomainException $e) {
            return redirect()->route('admin.tenants.edit', $tenant)->withErrors(['role' => $e->getMessage()]);
        }

        return redirect()->route('admin.tenants.edit', $tenant)->with('status', 'Role ditugaskan.');
    }

    public function destroy(Tenant $tenant, User $user): RedirectResponse
    {
        $this->authorize('assignRole', $tenant);

        try {
            $this->service->remove($tenant, $user);
        } catch (DomainException $e) {
            return redirect()->route('admin.tenants.edit', $tenant)->withErrors(['role' => $e->getMessage()]);
        }

        return redirect()->route('admin.tenants.edit', $tenant)->with('status', 'Role dicabut.');
    }
}
