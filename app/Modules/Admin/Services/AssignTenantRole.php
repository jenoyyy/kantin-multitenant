<?php

namespace App\Modules\Admin\Services;

use App\Models\Tenant;
use App\Models\TenantUserRole;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Penugasan / pencabutan role user pada tenant.
 * Satu user hanya punya satu role per tenant. Owner terakhir dilindungi.
 */
final class AssignTenantRole
{
    public function __construct(private AuditLogger $audit) {}

    public function assign(Tenant $tenant, User $user, string $role): TenantUserRole
    {
        return DB::transaction(function () use ($tenant, $user, $role): TenantUserRole {
            $existing = TenantUserRole::query()
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            // Menurunkan owner terakhir sama berbahayanya dengan mencopotnya.
            if ($existing?->role === 'owner' && $role !== 'owner') {
                $this->guardLastOwner($tenant);
            }

            $before = $existing?->only(['user_id', 'role']);

            $membership = TenantUserRole::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'user_id' => $user->id],
                ['role' => $role],
            );

            $this->audit->record('tenant.role_assigned', $tenant, [
                'before' => $before,
                'after' => ['user_id' => $user->id, 'role' => $role],
            ]);

            return $membership;
        });
    }

    public function remove(Tenant $tenant, User $user): void
    {
        DB::transaction(function () use ($tenant, $user): void {
            $existing = TenantUserRole::query()
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing === null) {
                return;
            }

            if ($existing->role === 'owner') {
                $this->guardLastOwner($tenant);
            }

            $before = $existing->only(['user_id', 'role']);
            $existing->delete();

            $this->audit->record('tenant.role_removed', $tenant, ['before' => $before]);
        });
    }

    private function guardLastOwner(Tenant $tenant): void
    {
        $owners = TenantUserRole::query()
            ->where('tenant_id', $tenant->id)
            ->where('role', 'owner')
            ->count();

        if ($owners <= 1) {
            throw new DomainException('Tidak boleh mencopot atau menurunkan owner terakhir tenant.');
        }
    }
}