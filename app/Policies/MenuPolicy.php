<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantRole;

class MenuPolicy
{
    use ChecksTenantRole;

    private const SEMUA = ['owner', 'staff', 'cashier'];
    private const PENGELOLA = ['owner', 'staff'];

    public function viewAny(User $user): bool
    {
        return $this->canActInContext($user, self::SEMUA);
    }

    public function view(User $user, Menu $menu): bool
    {
        return $this->canAct($user, (int) $menu->tenant_id, self::SEMUA);
    }

    public function create(User $user): bool
    {
        return $this->canActInContext($user, self::PENGELOLA);
    }

    public function update(User $user, Menu $menu): bool
    {
        return $this->canAct($user, (int) $menu->tenant_id, self::PENGELOLA);
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $this->canAct($user, (int) $menu->tenant_id, self::PENGELOLA);
    }
}