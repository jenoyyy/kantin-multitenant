<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;
use App\Policies\Concerns\ChecksTenantRole;

class WithdrawalPolicy
{
    use ChecksTenantRole;

    private const PEMILIK = ['owner'];

    public function viewAny(User $user): bool
    {
        return $this->canActInContext($user, self::PEMILIK);
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $this->canAct($user, (int) $withdrawal->tenant_id, self::PEMILIK);
    }

    public function create(User $user): bool
    {
        return $this->canActInContext($user, self::PEMILIK);
    }

    public function update(User $user, Withdrawal $withdrawal): bool
    {
        return false;
    }

    public function delete(User $user, Withdrawal $withdrawal): bool
    {
        return false;
    }
}