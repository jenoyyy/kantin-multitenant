<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Support\Auth\LoginLockout;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * UC-19: verifikasi kredensial + status akun, pesan generik, dan penguncian per akun
     * (5x gagal/10 menit -> terkunci 15 menit).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIpIsNotRateLimited();

        $lockout = app(LoginLockout::class);

        if ($lockout->isLocked($this->email)) {
            throw ValidationException::withMessages([
                'form.email' => 'Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam '.$lockout->minutesRemaining($this->email).' menit.',
            ]);
        }

        $user = User::query()->where('email', $this->email)->first();

        if ($user !== null && $user->status === 'active' && Hash::check($this->password, $user->password)) {
            $lockout->clear($this->email);
            RateLimiter::clear($this->ipKey());
            Auth::login($user, $this->remember);

            return;
        }

        RateLimiter::hit($this->ipKey());
        $lockout->recordFailure($this->email);

        // Pesan sama untuk surel tak dikenal, sandi salah, dan akun nonaktif.
        throw ValidationException::withMessages([
            'form.email' => 'Surel atau kata sandi tidak sesuai.',
        ]);
    }

    /**
     * Lapis per-IP terhadap brute force massal (20 percobaan/menit).
     */
    protected function ensureIpIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->ipKey(), 20)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->ipKey());

        throw ValidationException::withMessages([
            'form.email' => 'Terlalu banyak percobaan dari perangkat ini. Coba lagi dalam '.$seconds.' detik.',
        ]);
    }

    protected function ipKey(): string
    {
        return 'login-ip:'.(request()->ip() ?? 'unknown');
    }
}
