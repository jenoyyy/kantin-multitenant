<?php

namespace App\Policies;

use App\Models\TenantOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantRole;

class TenantOrderPolicy
{
    use ChecksTenantRole;

    private const SEMUA = ['owner', 'staff', 'cashier'];

    public function viewAny(User $user): bool
    {
        return $this->canActInContext($user, self::SEMUA);
    }

    public function view(User $user, TenantOrder $order): bool
    {
        return $this->canAct($user, (int) $order->tenant_id, self::SEMUA);
    }

    public function update(User $user, TenantOrder $order): bool
    {
        return $this->canAct($user, (int) $order->tenant_id, self::SEMUA);
    }

    public function delete(User $user, TenantOrder $order): bool
    {
        return false;
    }
}