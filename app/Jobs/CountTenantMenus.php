<?php

namespace App\Jobs;

use App\Models\Menu;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CountTenantMenus implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $tenantId,
    ) {
    }

    public function handle(): void
    {
        $tenant = Tenant::query()->findOrFail($this->tenantId);

        $context = app(TenantContext::class);
        $context->set($tenant);

        try {
            $count = Menu::query()->count();

            logger()->info('Tenant menu count', [
                'tenant_id' => $tenant->id,
                'count' => $count,
            ]);
        } finally {
            $context->clear();
        }
    }
}