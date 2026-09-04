<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Route pelanggan: publik, tidak perlu login
Route::prefix('kantin/{canteen:slug}')
    ->name('customer.')
    ->group(base_path('routes/customer.php'));

// Route tenant (operator): wajib login
Route::middleware(['auth', 'verified'])
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
