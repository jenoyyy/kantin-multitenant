<?php

use App\Modules\Admin\Http\Controllers\AdminBankAccountController;
use App\Modules\Admin\Http\Controllers\AdminCommissionController;
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
