<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\CustomerSession;
use App\Models\DiningTable;
use App\Models\TableQrToken;
use App\Models\User;
use App\Models\UserCanteenRole;
use App\Modules\Admin\Services\QrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrTableSessionTest extends TestCase
{
    use RefreshDatabase;

    private function canteen(string $status = 'active'): Canteen
    {
        return Canteen::factory()->create(['status' => $status]);
    }

    private function managerFor(Canteen $canteen): User
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        UserCanteenRole::create(['user_id' => $user->id, 'canteen_id' => $canteen->id, 'role' => 'manager']);

        return $user;
    }

    private function table(?Canteen $canteen = null, string $status = 'active'): DiningTable
    {
        $canteen ??= $this->canteen();

        return DiningTable::factory()->create(['canteen_id' => $canteen->id, 'status' => $status]);
    }

    public function test_dining_table_code_unique_per_canteen(): void
    {
        $canteenA = $this->canteen();
        $canteenB = $this->canteen();
        DiningTable::factory()->create(['canteen_id' => $canteenA->id, 'code' => 'T1']);

        // kode sama pada canteen sama -> ditolak
        $this->actingAs($this->managerFor($canteenA));
        $this->post(route('admin.tables.store'), ['code' => 'T1', 'label' => 'Meja Baru'])
            ->assertSessionHasErrors('code');

        // kode sama pada canteen berbeda -> boleh
        $this->actingAs($this->managerFor($canteenB));
        $this->post(route('admin.tables.store'), ['code' => 'T1', 'label' => 'Meja Baru'])
            ->assertRedirect(route('admin.tables.index'));
    }

    public function test_non_manager_cannot_manage_tables(): void
    {
        $outsider = User::factory()->create(['role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        $this->actingAs($outsider);

        $this->get(route('admin.tables.index'))->assertForbidden();
    }

    public function test_rotate_revokes_old_token_and_keeps_single_active(): void
    {
        $table = $this->table();
        $qr = app(QrTokenService::class);

        $plain1 = $qr->issue($table);
        $this->get(route('customer.scan', ['token' => $plain1]))->assertRedirect();

        $plain2 = $qr->rotate($table);

        // token lama gagal segera setelah rotasi
        $this->get(route('customer.scan', ['token' => $plain1]))->assertNotFound();
        // token baru sah
        $this->get(route('customer.scan', ['token' => $plain2]))->assertRedirect();

        $this->assertSame(1, TableQrToken::where('dining_table_id', $table->id)->where('status', 'active')->count());
    }

    public function test_valid_scan_creates_bound_session_with_secure_cookie(): void
    {
        $canteen = $this->canteen();
        $table = $this->table($canteen);
        $plain = app(QrTokenService::class)->issue($table);

        $response = $this->get(route('customer.scan', ['token' => $plain]));
        $response->assertRedirect(route('customer.welcome', ['canteen' => $canteen->code]));

        $session = CustomerSession::firstOrFail();
        $this->assertSame($canteen->id, $session->canteen_id);
        $this->assertSame($table->id, $session->dining_table_id);

        $response->assertCookie('customer_session');
        $cookie = $response->getCookie('customer_session', false);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_invalid_tokens_are_generic_404(): void
    {
        // acak
        $this->get(route('customer.scan', ['token' => 'tidak-ada-token']))->assertNotFound();

        // numeric id (anti-enumeration)
        $this->get(route('customer.scan', ['token' => '1']))->assertNotFound();

        // revoked
        $table = $this->table();
        $plain = app(QrTokenService::class)->issue($table);
        app(QrTokenService::class)->rotate($table); // mencabut $plain
        $this->get(route('customer.scan', ['token' => $plain]))->assertNotFound();

        // canteen nonaktif
        $t2 = $this->table($this->canteen('inactive'));
        $plain2 = app(QrTokenService::class)->issue($t2);
        $this->get(route('customer.scan', ['token' => $plain2]))->assertNotFound();

        // meja nonaktif
        $t3 = $this->table(null, 'inactive');
        $plain3 = app(QrTokenService::class)->issue($t3);
        $this->get(route('customer.scan', ['token' => $plain3]))->assertNotFound();
    }
}
