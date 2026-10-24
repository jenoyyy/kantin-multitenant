<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Canteen;
use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Models\TenantBalance;
use App\Models\TenantBankAccount;
use App\Models\User;
use App\Models\UserCanteenRole;
use App\Modules\Admin\Services\AssignTenantRole;
use App\Modules\Admin\Services\ChangeCommissionSchedule;
use App\Modules\Admin\Services\ManageBankAccount;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private function managerFor(Canteen $canteen): User
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        UserCanteenRole::create(['user_id' => $user->id, 'canteen_id' => $canteen->id, 'role' => 'manager']);

        return $user;
    }

    private function tenantIn(Canteen $canteen, array $attrs = []): Tenant
    {
        return Tenant::factory()->create(array_merge(['canteen_id' => $canteen->id], $attrs));
    }

    /**
     * Payload pembuatan tenant yang lengkap: identitas, penanggung jawab, dan rekening.
     *
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'display_name' => 'Warung Bu Rina', 'code' => 'RINA', 'slug' => 'warung-bu-rina', 'commission_rate' => '10',
            'pic_name' => 'Rina S.', 'pic_email' => 'rina@kantin.test',
            'bank_name' => 'BCA', 'account_holder_name' => 'Rina Sari', 'account_number' => '1234566721',
        ], $overrides);
    }

    public function test_only_canteen_manager_can_see_tenant_index(): void
    {
        $canteen = Canteen::factory()->create();
        $this->actingAs($this->managerFor($canteen));
        $this->get(route('admin.tenants.index'))->assertOk();

        // admin tanpa peran kantin -> 403 (policy viewAny)
        $outsider = User::factory()->create(['role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        $this->actingAs($outsider);
        $this->get(route('admin.tenants.index'))->assertForbidden();
    }

    public function test_creating_tenant_also_creates_active_commission_and_balance(): void
    {
        $canteen = Canteen::factory()->create();
        $this->actingAs($this->managerFor($canteen));

        $this->post(route('admin.tenants.store'), $this->payload([
            'display_name' => 'Bakso Enak', 'code' => 'BAKSO', 'slug' => 'bakso', 'commission_rate' => '15',
        ]))->assertRedirect();

        $tenant = Tenant::where('code', 'BAKSO')->firstOrFail();
        $this->assertSame(1, CommissionScheme::where('tenant_id', $tenant->id)->whereNull('valid_to')->count());
        $this->assertTrue(TenantBalance::where('tenant_id', $tenant->id)->exists());

        // Halaman create dan edit benar-benar merender.
        $this->get(route('admin.tenants.create'))->assertOk();
        $this->get(route('admin.tenants.edit', $tenant))->assertOk()->assertSee('Riwayat komisi');
    }

    public function test_tenant_code_is_unique_per_canteen(): void
    {
        $canteen = Canteen::factory()->create();
        $manager = $this->managerFor($canteen);
        $this->tenantIn($canteen, ['code' => 'DUP']);

        $this->actingAs($manager);
        $this->post(route('admin.tenants.store'), $this->payload([
            'display_name' => 'X', 'code' => 'DUP', 'slug' => 'x', 'commission_rate' => '10',
        ]))->assertSessionHasErrors('code');
    }

    public function test_commission_schedule_is_effective_dated(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());
        CommissionScheme::create([
            'tenant_id' => $tenant->id, 'commission_rate' => 0.15,
            'valid_from' => now()->subMonth(), 'valid_to' => null,
        ]);

        app(ChangeCommissionSchedule::class)->handle($tenant, 0.20, now()->addDay());

        $schemes = CommissionScheme::where('tenant_id', $tenant->id)->orderBy('valid_from')->get();
        $this->assertCount(2, $schemes);
        $this->assertNotNull($schemes[0]->valid_to, 'versi lama harus ditutup');
        $this->assertNull($schemes[1]->valid_to, 'versi baru harus aktif');
    }

    public function test_commission_schedule_rejects_time_before_active_version(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());
        CommissionScheme::create([
            'tenant_id' => $tenant->id, 'commission_rate' => 0.15,
            'valid_from' => now()->addDays(5), 'valid_to' => null,
        ]);

        $this->expectException(DomainException::class);
        app(ChangeCommissionSchedule::class)->handle($tenant, 0.20, now()->addDay());
    }

    public function test_two_open_commission_schemes_rejected_by_db_guard(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());
        $row = fn () => [
            'tenant_id' => $tenant->id, 'commission_rate' => 0.15,
            'valid_from' => now(), 'valid_to' => null, 'created_at' => now(), 'updated_at' => now(),
        ];
        DB::table('commission_schemes')->insert($row());

        $this->expectException(QueryException::class);
        DB::table('commission_schemes')->insert($row());
    }

    public function test_bank_account_is_stored_encrypted_with_last4(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());

        $account = app(ManageBankAccount::class)->store($tenant, [
            'bank_name' => 'BCA', 'account_holder_name' => 'Budi', 'account_number' => '1234567890',
        ]);

        $raw = DB::table('bank_accounts')->where('id', $account->id)->first();
        $this->assertStringNotContainsString('1234567890', (string) $raw->account_number_encrypted, 'nomor mentah bocor di DB');
        $this->assertSame('7890', $raw->account_number_last4);
        $this->assertSame('1234567890', $account->fresh()->account_number_encrypted, 'harus bisa didekripsi kembali');
    }

    public function test_audit_log_never_contains_full_account_number(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());

        app(ManageBankAccount::class)->store($tenant, [
            'bank_name' => 'BCA', 'account_holder_name' => 'Budi', 'account_number' => '1234567890',
        ]);

        $log = AuditLog::where('action', 'tenant.bank_account_created')->firstOrFail();
        $this->assertStringNotContainsString('1234567890', json_encode($log->changes));
        $this->assertStringContainsString('7890', json_encode($log->changes));
    }

    public function test_only_verified_account_can_become_primary_and_only_one_primary(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());
        $svc = app(ManageBankAccount::class);
        $a = $svc->store($tenant, ['bank_name' => 'BCA', 'account_holder_name' => 'A', 'account_number' => '111111']);
        $b = $svc->store($tenant, ['bank_name' => 'BNI', 'account_holder_name' => 'B', 'account_number' => '222222']);

        $svc->verify($tenant, $a, true);
        $svc->verify($tenant, $b, true);
        $svc->makePrimary($tenant, $a);
        $svc->makePrimary($tenant, $b);

        $this->assertFalse($a->fresh()->is_primary);
        $this->assertTrue($b->fresh()->is_primary);
        $this->assertSame(1, TenantBankAccount::where('tenant_id', $tenant->id)->where('is_primary', true)->count());
    }

    public function test_unverified_account_cannot_become_primary(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());
        $svc = app(ManageBankAccount::class);
        $a = $svc->store($tenant, ['bank_name' => 'BCA', 'account_holder_name' => 'A', 'account_number' => '111111']);

        $this->expectException(DomainException::class);
        $svc->makePrimary($tenant, $a);
    }

    public function test_cannot_remove_last_owner(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());
        $owner = User::factory()->create();
        $svc = app(AssignTenantRole::class);
        $svc->assign($tenant, $owner, 'owner');

        $this->expectException(DomainException::class);
        $svc->remove($tenant, $owner);
    }

    public function test_cannot_demote_last_owner(): void
    {
        $tenant = $this->tenantIn(Canteen::factory()->create());
        $owner = User::factory()->create();
        $svc = app(AssignTenantRole::class);
        $svc->assign($tenant, $owner, 'owner');

        $this->expectException(DomainException::class);
        $svc->assign($tenant, $owner, 'staff');
    }

    public function test_audit_log_is_written_on_sensitive_action(): void
    {
        $canteen = Canteen::factory()->create();
        $this->actingAs($this->managerFor($canteen));

        $this->post(route('admin.tenants.store'), $this->payload([
            'display_name' => 'Y', 'code' => 'YY', 'slug' => 'yy', 'commission_rate' => '12',
        ]))->assertRedirect();

        $this->assertTrue(AuditLog::where('action', 'tenant.created')->where('target_type', 'Tenant')->exists());
    }
}
