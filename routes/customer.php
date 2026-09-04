<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function (string $canteen) {
    return view('customer.home', ['canteen' => $canteen]);
})->name('home');