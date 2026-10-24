<?php

namespace Tests\Feature\Auth;

use App\Models\Canteen;
use App\Models\Tenant;
use App\Models\TenantUserRole;
use App\Models\User;
use App\Support\Auth\LoginLockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * UC-19 Autentikasi Pengguna: pesan generik, penguncian 5x/10 menit selama 15 menit,
 * akun nonaktif ditolak, redirect dasbor per peran, dan sesi 8 jam.
 */
class LoginLockoutTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC = 'Surel atau kata sandi tidak sesuai.';

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login-ip:127.0.0.1');
    }

    private function attempt(string $email, string $password)
    {
        return Volt::test('pages.auth.login')
            ->set('form.email', $email)
            ->set('form.password', $password)
            ->call('login');
    }

    private function activeUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['status' => 'active'], $attrs));
    }

    public function test_wrong_password_and_unknown_email_get_the_same_generic_message(): void
    {
        $user = $this->activeUser();

        $first = $this->attempt($user->email, 'salah-sandi');
        $second = $this->attempt('tidak-ada@kantin.test', 'apa-saja');

        $this->assertSame(self::GENERIC, $first->errors()->first('form.email'));
        $this->assertSame(self::GENERIC, $second->errors()->first('form.email'));
        $this->assertGuest();
    }

    public function test_five_failures_within_ten_minutes_lock_the_account_even_with_correct_password(): void
    {
        $user = $this->activeUser();

        foreach (range(1, LoginLockout::MAX_FAILURES) as $ignored) {
            $this->attempt($user->email, 'salah-sandi');
        }

        $locked = $this->attempt($user->email, 'password');

        $this->assertStringContainsString('Akun terkunci sementara', $locked->errors()->first('form.email'));
        $this->assertGuest();
    }

    public function test_lock_expires_after_fifteen_minutes(): void
    {
        $user = $this->activeUser();
        foreach (range(1, LoginLockout::MAX_FAILURES) as $ignored) {
            $this->attempt($user->email, 'salah-sandi');
        }

        $this->travel(14)->minutes();
        $this->attempt($user->email, 'password')->assertHasErrors(['form.email']);

        $this->travel(2)->minutes();
        $this->attempt($user->email, 'password')->assertHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_failures_spread_beyond_the_ten_minute_window_do_not_lock(): void
    {
        $user = $this->activeUser();
        foreach (range(1, LoginLockout::MAX_FAILURES - 1) as $ignored) {
            $this->attempt($user->email, 'salah-sandi');
        }

        $this->travel(11)->minutes();
        $this->attempt($user->email, 'salah-sandi');

        $this->attempt($user->email, 'password')->assertHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_successful_login_resets_the_failure_counter(): void
    {
        $user = $this->activeUser();
        foreach (range(1, LoginLockout::MAX_FAILURES - 1) as $ignored) {
            $this->attempt($user->email, 'salah-sandi');
        }
        $this->attempt($user->email, 'password')->assertHasNoErrors();
        Auth::logout();

        $this->attempt($user->email, 'salah-sandi');
        $this->attempt($user->email, 'password')->assertHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_account_is_rejected_with_generic_message(): void
    {
        $user = $this->activeUser(['status' => 'suspended']);

        $result = $this->attempt($user->email, 'password');

        $this->assertSame(self::GENERIC, $result->errors()->first('form.email'));
        $this->assertGuest();
    }

    public function test_successful_login_goes_through_the_dashboard_redirector(): void
    {
        $user = $this->activeUser();

        $this->attempt($user->email, 'password')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = $this->activeUser(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_tenant_operator_is_redirected_to_own_tenant_dashboard(): void
    {
        $tenant = Tenant::factory()->create(['canteen_id' => Canteen::factory()->create()->id]);
        $operator = $this->activeUser(['role' => 'tenant']);
        TenantUserRole::create(['user_id' => $operator->id, 'tenant_id' => $tenant->id, 'role' => 'staff']);

        $this->actingAs($operator)->get(route('dashboard'))
            ->assertRedirect(route('tenant.dashboard', ['tenant' => $tenant->slug]));
    }

    public function test_login_page_is_indonesian_and_states_the_policy(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk')
            ->assertSee('Kata sandi')
            ->assertSee('5× gagal dalam 10 menit → akun terkunci 15 menit.');
    }

    public function test_session_expires_after_eight_hours_of_inactivity(): void
    {
        $this->assertSame(480, config('session.lifetime'));
    }
}
