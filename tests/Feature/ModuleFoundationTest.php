<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_login_saat_membuka_halaman_tenant(): void
    {
        $response = $this->get('/tenant/tenant-a/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_tamu_diarahkan_ke_login_saat_membuka_halaman_admin(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_dengan_role_admin_bisa_membuka_dashboard_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
    }

    public function test_pelanggan_bisa_membuka_halaman_kantin_tanpa_login(): void
    {
        $response = $this->get('/kantin/kantin-pusat');

        $response->assertOk();
    }
}
