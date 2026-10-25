<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Modules\Ordering\Http\Controllers\ResolveTableQrController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Entry point pelanggan via QR meja: token opaque di URL, dibatasi 30 pindai/menit per IP
// (mengurangi brute force token). Kantin ditentukan dari token, bukan dari input klien.
RateLimiter::for('qr-scan', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip() ?? 'unknown'));

Route::get('/q/{token}', ResolveTableQrController::class)
    ->middleware('throttle:qr-scan')
    ->name('customer.scan');

// Route pelanggan: publik, tidak perlu login
Route::prefix('kantin/{canteen:code}')
    ->name('customer.')
    ->group(base_path('routes/customer.php'));

// Route tenant (operator): wajib login
Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('tenant/{tenant:slug}')
    ->scopeBindings()
    ->name('tenant.')
    ->group(base_path('routes/tenant.php'));

// Route admin (pengelola kantin): wajib login
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

require __DIR__.'/auth.php';
