<?php

use App\Modules\Admin\Http\Controllers\AdminBankAccountController;
use App\Modules\Admin\Http\Controllers\AdminCommissionController;
use App\Modules\Admin\Http\Controllers\AdminDiningTableController;
use App\Modules\Admin\Http\Controllers\AdminTenantController;
use App\Modules\Admin\Http\Controllers\AdminTenantRoleController;
use App\Modules\Admin\Http\Controllers\AdminTenantStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard');

Route::get('/tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
Route::get('/tenants/create', [AdminTenantController::class, 'create'])->name('tenants.create');
Route::post('/tenants', [AdminTenantController::class, 'store'])->name('tenants.store');
Route::get('/tenants/{tenant}/edit', [AdminTenantController::class, 'edit'])->name('tenants.edit');

// UC-21 alur 3a: aktifkan / nonaktifkan tenant.
Route::post('/tenants/{tenant}/status', AdminTenantStatusController::class)->name('tenants.status');

Route::post('/tenants/{tenant}/roles', [AdminTenantRoleController::class, 'store'])->name('tenants.roles.store');
Route::delete('/tenants/{tenant}/roles/{user}', [AdminTenantRoleController::class, 'destroy'])->name('tenants.roles.destroy');

Route::post('/tenants/{tenant}/commission', [AdminCommissionController::class, 'store'])->name('tenants.commission.store');

Route::post('/tenants/{tenant}/bank-accounts', [AdminBankAccountController::class, 'store'])->name('tenants.bank.store');
Route::post('/tenants/{tenant}/bank-accounts/{account}/verify', [AdminBankAccountController::class, 'verify'])->name('tenants.bank.verify');
Route::post('/tenants/{tenant}/bank-accounts/{account}/primary', [AdminBankAccountController::class, 'primary'])->name('tenants.bank.primary');

// UC-22: meja & QR (aksi rotate/QR/status ditambahkan di Tahap 4 dan 8).
Route::get('/tables', [AdminDiningTableController::class, 'index'])->name('tables.index');
Route::post('/tables', [AdminDiningTableController::class, 'store'])->name('tables.store');
Route::post('/tables/{table}/rotate', [AdminDiningTableController::class, 'rotate'])->name('tables.rotate');
Route::get('/tables/{table}/qr', [AdminDiningTableController::class, 'qr'])->name('tables.qr');
Route::post('/tables/{table}/status', [AdminDiningTableController::class, 'status'])->name('tables.status');
