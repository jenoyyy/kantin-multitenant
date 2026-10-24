<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * UC-19: proses masuk. Verifikasi dan penguncian akun ada di LoginForm;
     * pemilihan dasbor sesuai peran ada di DashboardRedirectController.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-4">
        <h1 class="text-lg font-semibold text-gray-800">Masuk</h1>
        <p class="text-sm text-gray-600">Gunakan akun tenant atau pengelola Anda.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Surel" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="nama@kantin.test" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Kata sandi" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Kebijakan UC-19 ditampilkan agar pengguna tahu konsekuensi percobaan gagal. -->
        <p class="mt-4 text-xs text-gray-600" data-test="lockout-policy">
            5× gagal dalam 10 menit → akun terkunci 15 menit. Sesi berakhir setelah 8 jam tidak aktif.
        </p>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                    Lupa kata sandi?
                </a>
            @endif

            <x-primary-button class="ms-3" data-test="login-button">
                Masuk →
            </x-primary-button>
        </div>
    </form>
</div>