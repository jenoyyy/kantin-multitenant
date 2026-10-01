<?php

namespace Tests\Feature;

use App\Jobs\CountTenantMenus;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Tenant;
use App\Models\TenantUserRole;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenantA;

    private Tenant $tenantB;

    private User $ownerA;

    private User $ownerB;

    private Menu $menuA;

    private Menu $menuB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::factory()->create();
        $this->tenantB = Tenant::factory()->create();

        $this->ownerA = $this->makeMember($this->tenantA, 'owner');
        $this->ownerB = $this->makeMember($this->tenantB, 'owner');

        $this->menuA = $this->makeMenu($this->tenantA, 'Menu Tenant A');
        $this->menuB = $this->makeMenu($this->tenantB, 'Menu Tenant B');
    }

    protected function tearDown(): void
    {
        app(TenantContext::class)->clear();

        parent::tearDown();
    }

    private function makeMember(Tenant $tenant, string $role): User
    {
        $user = User::factory()->create();

        $member = new TenantUserRole;
        $member->tenant_id = $tenant->id;
        $member->user_id = $user->id;
        $member->role = $role;
        $member->save();

        return $user;
    }

    private function makeMenu(Tenant $tenant, string $name): Menu
    {
        $category = MenuCategory::factory()->create(['tenant_id' => $tenant->id]);

        return Menu::factory()->create([
            'tenant_id' => $tenant->id,
            'menu_category_id' => $category->id,
            'name' => $name,
        ]);
    }

    // ---- Lapis HTTP: middleware, scoped binding, policy ----

    public function test_index_hanya_menampilkan_menu_tenant_sendiri(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->getJson("/tenant/{$this->tenantA->slug}/menus");

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Menu Tenant A']);
        $response->assertJsonMissing(['name' => 'Menu Tenant B']);
    }

    public function test_user_tenant_a_tidak_bisa_masuk_ke_url_tenant_b(): void
    {
        $this->actingAs($this->ownerA)
            ->getJson("/tenant/{$this->tenantB->slug}/menus")
            ->assertForbidden();

        $this->actingAs($this->ownerA)
            ->getJson("/tenant/{$this->tenantB->slug}/menus/{$this->menuB->id}")
            ->assertForbidden();
    }

    public function test_detail_menu_tenant_b_lewat_url_tenant_a_menghasilkan_404(): void
    {
        $response = $this->actingAs($this->ownerA)
            ->getJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuB->id}");

        $response->assertNotFound();
        $this->assertStringNotContainsString('Menu Tenant B', $response->getContent());
    }

    public function test_update_menu_tenant_b_lewat_url_tenant_a_ditolak_dan_data_tidak_berubah(): void
    {
        $this->actingAs($this->ownerA)
            ->putJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuB->id}", [
                'name' => 'Dibajak',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('menus', [
            'id' => $this->menuB->id,
            'name' => 'Menu Tenant B',
        ]);
    }

    public function test_hapus_menu_tenant_b_lewat_url_tenant_a_ditolak_dan_data_tetap_ada(): void
    {
        $this->actingAs($this->ownerA)
            ->deleteJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('menus', ['id' => $this->menuB->id]);
    }

    public function test_owner_boleh_mengubah_menu_sendiri(): void
    {
        $this->actingAs($this->ownerA)
            ->putJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuA->id}", [
                'name' => 'Nama Baru',
            ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Nama Baru']);

        $this->assertDatabaseHas('menus', [
            'id' => $this->menuA->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_kasir_boleh_melihat_tetapi_tidak_boleh_mengubah_menu(): void
    {
        $cashier = $this->makeMember($this->tenantA, 'cashier');

        $this->actingAs($cashier)
            ->getJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuA->id}")
            ->assertOk();

        $this->actingAs($cashier)
            ->putJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuA->id}", [
                'name' => 'Diubah Kasir',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('menus', [
            'id' => $this->menuA->id,
            'name' => 'Menu Tenant A',
        ]);
    }

    public function test_tenant_id_pada_payload_diabaikan(): void
    {
        $this->actingAs($this->ownerA)
            ->putJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuA->id}", [
                'name' => 'Coba Pindah',
                'tenant_id' => $this->tenantB->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('menus', [
            'id' => $this->menuA->id,
            'tenant_id' => $this->tenantA->id,
        ]);
    }

    // ---- Lapis model: policy dan global scope ----

    public function test_policy_menolak_menu_tenant_lain_walau_scope_dilewati(): void
    {
        app(TenantContext::class)->set($this->tenantA);

        $menuB = Menu::withoutGlobalScopes()->findOrFail($this->menuB->id);

        $this->assertFalse(Gate::forUser($this->ownerA)->allows('view', $menuB));
        $this->assertFalse(Gate::forUser($this->ownerA)->allows('update', $menuB));
        $this->assertFalse(Gate::forUser($this->ownerA)->allows('delete', $menuB));
    }

    public function test_hanya_owner_boleh_mengakses_withdrawal(): void
    {
        $staff = $this->makeMember($this->tenantA, 'staff');

        app(TenantContext::class)->set($this->tenantA);

        $this->assertTrue(Gate::forUser($this->ownerA)->allows('viewAny', Withdrawal::class));
        $this->assertFalse(Gate::forUser($staff)->allows('viewAny', Withdrawal::class));
        $this->assertFalse(Gate::forUser($this->ownerB)->allows('viewAny', Withdrawal::class));
    }

    public function test_global_scope_hanya_mengembalikan_data_tenant_aktif(): void
    {
        app(TenantContext::class)->set($this->tenantA);

        $this->assertSame(1, Menu::query()->count());
        $this->assertNull(Menu::query()->find($this->menuB->id));
        $this->assertNotNull(Menu::query()->find($this->menuA->id));
    }

    public function test_tenant_id_terisi_otomatis_dari_context(): void
    {
        app(TenantContext::class)->set($this->tenantA);

        $menu = new Menu;
        $menu->menu_category_id = $this->menuA->menu_category_id;
        $menu->name = 'Menu Baru';
        $menu->price_amount = 10000;
        $menu->is_available = true;
        $menu->save();

        $this->assertEquals($this->tenantA->id, $menu->tenant_id);
    }

    public function test_simpan_tanpa_context_dan_tanpa_tenant_id_ditolak(): void
    {
        $this->expectException(RuntimeException::class);

        $menu = new Menu;
        $menu->menu_category_id = $this->menuA->menu_category_id;
        $menu->name = 'Tanpa Tenant';
        $menu->price_amount = 10000;
        $menu->is_available = true;
        $menu->save();
    }

    // ---- Lapis job ----

    public function test_job_membentuk_context_sendiri_dan_membersihkannya(): void
    {
        Log::spy();

        $this->makeMenu($this->tenantB, 'Menu B kedua');

        (new CountTenantMenus($this->tenantA->id))->handle();
        $this->assertFalse(app(TenantContext::class)->has());

        (new CountTenantMenus($this->tenantB->id))->handle();
        $this->assertFalse(app(TenantContext::class)->has());

        Log::shouldHaveReceived('info')
            ->with('Tenant menu count', ['tenant_id' => $this->tenantA->id, 'count' => 1])
            ->once();

        Log::shouldHaveReceived('info')
            ->with('Tenant menu count', ['tenant_id' => $this->tenantB->id, 'count' => 2])
            ->once();
    }

    public function test_job_tenant_nonaktif_tidak_dijalankan(): void
    {
        Log::spy();

        $this->tenantA->forceFill(['status' => 'suspended'])->save();

        (new CountTenantMenus($this->tenantA->id))->handle();

        Log::shouldNotHaveReceived('info');
        $this->assertFalse(app(TenantContext::class)->has());
    }
}
