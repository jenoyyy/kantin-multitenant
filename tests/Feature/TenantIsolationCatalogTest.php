<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Tenant;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use App\Support\Tenancy\TenantContext;
use FilesystemIterator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class TenantIsolationCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        app(TenantContext::class)->clear();

        parent::tearDown();
    }

    private function makeMenu(Tenant $tenant, string $name, bool $available = true): Menu
    {
        $category = MenuCategory::factory()->create(['tenant_id' => $tenant->id]);

        return Menu::factory()->create([
            'tenant_id' => $tenant->id,
            'menu_category_id' => $category->id,
            'name' => $name,
            'is_available' => $available,
        ]);
    }

    public function test_katalog_hanya_memuat_menu_tenant_aktif_dari_kantin_terpilih(): void
    {
        $kantin1 = Canteen::factory()->create();
        $kantin2 = Canteen::factory()->create();

        $aktif1 = Tenant::factory()->create(['canteen_id' => $kantin1->id]);
        $aktif2 = Tenant::factory()->create(['canteen_id' => $kantin1->id]);
        $suspended = Tenant::factory()->create(['canteen_id' => $kantin1->id, 'status' => 'suspended']);
        $kantinLain = Tenant::factory()->create(['canteen_id' => $kantin2->id]);

        $this->makeMenu($aktif1, 'Nasi Goreng');
        $this->makeMenu($aktif2, 'Mie Ayam');
        $this->makeMenu($aktif1, 'Menu Habis', false);
        $this->makeMenu($suspended, 'Menu Tenant Suspended');
        $this->makeMenu($kantinLain, 'Menu Kantin Lain');

        $names = app(PublicCatalogQuery::class)
            ->menusForCanteen($kantin1)
            ->pluck('name')
            ->all();

        $this->assertEqualsCanonicalizing(['Nasi Goreng', 'Mie Ayam'], $names);
    }

    public function test_katalog_tetap_memuat_semua_tenant_aktif_walau_context_terisi(): void
    {
        $kantin = Canteen::factory()->create();
        $tenantA = Tenant::factory()->create(['canteen_id' => $kantin->id]);
        $tenantB = Tenant::factory()->create(['canteen_id' => $kantin->id]);

        $this->makeMenu($tenantA, 'Menu A');
        $this->makeMenu($tenantB, 'Menu B');

        app(TenantContext::class)->set($tenantA);

        $names = app(PublicCatalogQuery::class)
            ->menusForCanteen($kantin)
            ->pluck('name')
            ->all();

        $this->assertEqualsCanonicalizing(['Menu A', 'Menu B'], $names);
    }

    public function test_bypass_global_scope_hanya_ada_di_class_katalog_publik(): void
    {
        $allowed = str_replace('\\', '/', app_path('Modules/Catalog/Services/PublicCatalogQuery.php'));
        $violations = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $path = str_replace('\\', '/', $file->getPathname());

            if ($path === $allowed) {
                continue;
            }

            if (str_contains((string) file_get_contents($path), 'withoutGlobalScope')) {
                $violations[] = $path;
            }
        }

        $this->assertSame([], $violations, 'withoutGlobalScope(s) dipakai di luar PublicCatalogQuery.');
    }
}
