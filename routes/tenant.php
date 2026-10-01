<?php

use App\Http\Controllers\Tenant\MenuController;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', fn (Tenant $tenant) => view('tenant.dashboard', ['tenant' => $tenant]))
    ->name('dashboard');

Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
Route::get('/menus/{menu}', [MenuController::class, 'show'])->name('menus.show');
Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
