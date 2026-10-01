<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\Tenant;
use App\Models\TenantUserRole;
use App\Models\User;
use App\Providers\AdminServiceProvider;
use App\Providers\CatalogServiceProvider;
use App\Providers\KitchenServiceProvider;
use App\Providers\OrderingServiceProvider;
use App\Providers\PaymentsServiceProvider;
use App\Providers\ReportingServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModularFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_route_is_public(): void
    {
        $canteen = Canteen::factory()->create();

        $response = $this->get("/kantin/{$canteen->code}");

        $response->assertSuccessful();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect();
    }

    public function test_tenant_member_can_access_tenant_dashboard(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();

        $role = new TenantUserRole;
        $role->user_id = $user->id;
        $role->tenant_id = $tenant->id;
        $role->save();

        $response = $this
            ->actingAs($user)
            ->get("/tenant/{$tenant->slug}/dashboard");

        $response->assertSuccessful();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertSuccessful();
    }

    public function test_non_member_cannot_access_tenant_dashboard(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get("/tenant/{$tenant->slug}/dashboard");

        $response->assertForbidden();
    }

    public function test_user_without_tenant_role_cannot_access_tenant_dashboard(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get("/tenant/{$tenant->slug}/dashboard");

        $response->assertForbidden();
    }

    public function test_suspended_tenant_is_forbidden(): void
    {
        $user = User::factory()->create();

        $tenant = Tenant::factory()->create([
            'status' => 'suspended',
        ]);

        $role = new TenantUserRole;
        $role->user_id = $user->id;
        $role->tenant_id = $tenant->id;
        $role->save();

        $response = $this
            ->actingAs($user)
            ->get("/tenant/{$tenant->slug}/dashboard");

        $response->assertForbidden();
    }

    public function test_all_module_providers_are_registered(): void
    {
        $providers = [
            AdminServiceProvider::class,
            CatalogServiceProvider::class,
            OrderingServiceProvider::class,
            PaymentsServiceProvider::class,
            KitchenServiceProvider::class,
            ReportingServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->assertTrue(
                app()->getProvider($provider) !== null,
                "Provider {$provider} belum terdaftar."
            );
        }
    }
}
