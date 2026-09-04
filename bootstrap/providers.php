<?php

use App\Providers\AppServiceProvider;
use App\Providers\VoltServiceProvider;
use App\Providers\AdminServiceProvider;
use App\Providers\CatalogServiceProvider;
use App\Providers\OrderingServiceProvider;
use App\Providers\PaymentsServiceProvider;
use App\Providers\KitchenServiceProvider;
use App\Providers\ReportingServiceProvider;

return [
    AppServiceProvider::class,
    VoltServiceProvider::class,
    // Modular monolith — satu provider per bounded context (Modul 2)
    AdminServiceProvider::class,
    CatalogServiceProvider::class,
    OrderingServiceProvider::class,
    PaymentsServiceProvider::class,
    KitchenServiceProvider::class,
    ReportingServiceProvider::class,
];