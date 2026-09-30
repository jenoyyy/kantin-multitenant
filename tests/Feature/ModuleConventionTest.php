<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleConventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_view_namespaces_are_registered(): void
    {
        $this->assertTrue(
            view()->exists('customer.home')
        );

        $this->assertTrue(
            view()->exists('tenant.dashboard')
        );

        $this->assertTrue(
            view()->exists('admin.dashboard')
        );
    }

    public function test_module_routes_are_loaded(): void
    {
        $this->assertTrue(
            collect(app('router')->getRoutes()->getRoutes())
                ->contains(fn ($route) => $route->getName() === 'tenant.dashboard')
        );
    }

    public function test_tenant_route_has_expected_middleware(): void
    {
        $route = collect(app('router')->getRoutes()->getRoutes())
            ->first(fn ($route) => $route->getName() === 'tenant.dashboard');

        $this->assertNotNull($route);

        $middleware = $route->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains('verified', $middleware);
        $this->assertContains('tenant', $middleware);
    }

    public function test_livewire_namespace_is_registered(): void
    {
        $this->assertTrue(
            class_exists(\App\Livewire\Actions\Logout::class)
        );
    }

    public function test_portal_routes_use_expected_middleware(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());

        $tenantRoute = $routes->first(
            fn ($route) => str_starts_with($route->uri(), 'tenant/')
        );

        $this->assertNotNull($tenantRoute);

        $middleware = $tenantRoute->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains('verified', $middleware);
        $this->assertContains('tenant', $middleware);
    }
}