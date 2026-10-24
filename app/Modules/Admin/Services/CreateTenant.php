<?php

namespace App\Modules\Admin\Services;

use App\Models\Canteen;
use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Models\TenantBalance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * UC-21 onboarding: membuat tenant, komisi aktif awal, saldo nol, akun penanggung jawab
 * (owner) dan rekening bank dalam satu transaksi. Kredensial awal dikirim SETELAH commit
 * berupa tautan atur-kata-sandi, bukan kata sandi mentah: kata sandi acak awal tidak
 * pernah diketahui siapa pun.
 */
final class CreateTenant
{
    public function __construct(
        private AuditLogger $audit,
        private ManageBankAccount $bankAccounts,
        private AssignTenantRole $roles,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(Canteen $canteen, array $data): Tenant
    {
        return DB::transaction(function () use ($canteen, $data): Tenant {
            $tenant = (new Tenant)->forceFill([
                'canteen_id' => $canteen->id,
                'code' => $data['code'],
                'slug' => $data['slug'],
                'display_name' => $data['display_name'],
                'status' => 'active',
            ]);
            $tenant->save();

            (new TenantBalance)->forceFill([
                'tenant_id' => $tenant->id,
                'amount' => 0,
            ])->save();

            (new CommissionScheme)->forceFill([
                'tenant_id' => $tenant->id,
                'commission_rate' => $data['commission_rate'],
                'valid_from' => now(),
                'valid_to' => null,
            ])->save();

            $this->audit->record('tenant.created', $tenant, [
                'after' => $tenant->only(['code', 'slug', 'display_name', 'status']),
            ]);

            if (isset($data['pic_email'])) {
                $owner = (new User)->forceFill([
                    'name' => $data['pic_name'],
                    'email' => $data['pic_email'],
                    'password' => Str::password(32),
                    'role' => 'tenant',
                    'status' => 'active',
                    'email_verified_at' => now(), // diverifikasi pengelola saat onboarding
                ]);
                $owner->save();

                $this->roles->assign($tenant, $owner, 'owner');

                DB::afterCommit(fn () => Password::broker()->sendResetLink(['email' => $owner->email]));
            }

            if (isset($data['account_number'])) {
                $this->bankAccounts->store($tenant, $data)
                    ->forceFill(['is_primary' => true])
                    ->save();
            }

            return $tenant;
        });
    }
}
