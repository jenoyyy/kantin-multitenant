<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', fn (Tenant $tenant) => view('tenant.dashboard', ['tenant' => $tenant]))
    ->name('dashboard');