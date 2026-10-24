<?php

namespace App\Modules\Admin\Services;

use App\Models\Tenant;
use App\Models\TenantBankAccount;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Rekening: simpan nomor terenkripsi + last4, verifikasi status, satu rekening utama.
 */
final class ManageBankAccount
{
    public function __construct(private AuditLogger $audit) {}

    /** @param array<string, mixed> $data */
    public function store(Tenant $tenant, array $data): TenantBankAccount
    {
        return DB::transaction(function () use ($tenant, $data): TenantBankAccount {
            $number = (string) $data['account_number'];

            $account = (new TenantBankAccount)->forceFill([
                'tenant_id' => $tenant->id,
                'bank_name' => $data['bank_name'],
                'account_holder_name' => $data['account_holder_name'],
                'account_number_last4' => Str::substr($number, -4),
                'account_number_encrypted' => $number, // dienkripsi oleh cast
                'status' => 'unverified',
                'is_primary' => false,
            ]);
            $account->save();

            // Audit tanpa nomor mentah.
            $this->audit->record('tenant.bank_account_created', $tenant, [
                'after' => [
                    'bank_name' => $account->bank_name,
                    'account_number_last4' => $account->account_number_last4,
                ],
            ]);

            return $account;
        });
    }

    public function verify(Tenant $tenant, TenantBankAccount $account, bool $approve): void
    {
        $before = $account->status;
        $account->forceFill(['status' => $approve ? 'verified' : 'rejected'])->save();

        // Rekening yang ditolak tidak boleh tetap menjadi rekening utama.
        if (! $approve && $account->is_primary) {
            $account->forceFill(['is_primary' => false])->save();
        }

        $this->audit->record('tenant.bank_account_'.($approve ? 'verified' : 'rejected'), $tenant, [
            'before' => ['status' => $before],
            'after' => ['status' => $account->status, 'account_number_last4' => $account->account_number_last4],
        ]);
    }

    public function makePrimary(Tenant $tenant, TenantBankAccount $account): void
    {
        if ($account->status !== 'verified') {
            throw new DomainException('Hanya rekening yang sudah diverifikasi yang boleh dijadikan utama.');
        }

        DB::transaction(function () use ($tenant, $account): void {
            // Matikan yang lama dulu, baru nyalakan yang baru (pengaman DB satu primary).
            TenantBankAccount::query()
                ->where('tenant_id', $account->tenant_id)
                ->whereKeyNot($account->id)
                ->update(['is_primary' => false]);

            $account->forceFill(['is_primary' => true])->save();

            $this->audit->record('tenant.bank_account_primary', $tenant, [
                'after' => ['account_number_last4' => $account->account_number_last4],
            ]);
        });
    }
}
