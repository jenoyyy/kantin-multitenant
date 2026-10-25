<?php

use App\Modules\Ordering\Http\Controllers\CustomerWelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function (string $canteen) {
    return view('customer.home', ['canteen' => $canteen]);
})->name('home');

// UC-02: halaman sambutan sesudah pindai QR meja.
Route::get('/meja', CustomerWelcomeController::class)->name('welcome');
