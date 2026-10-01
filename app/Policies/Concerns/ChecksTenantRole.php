<?php

namespace App\Policies\Concerns;

use App\Models\TenantUserRole;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

trait ChecksTenantRole
{
    /**
     * Boleh hanya jika objek milik tenant aktif DAN user punya salah satu role.
     */
    protected function canAct(User $user, int $tenantId, array $roles): bool
    {
        $context = app(TenantContext::class);

        if (! $context->has() || $context->id() !== $tenantId) {
            return false;
        }

        $role = TenantUserRole::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->value('role');

        return in_array($role, $roles, true);
    }

    /**
     * Untuk aksi tanpa objek (daftar, buat baru): pakai tenant aktif.
     */
    protected function canActInContext(User $user, array $roles): bool
    {
        $context = app(TenantContext::class);

        return $context->has()
            && $this->canAct($user, $context->id(), $roles);
    }
}
