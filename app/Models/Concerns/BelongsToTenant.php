<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);

            if ($context->has()) {
                $builder->where(
                    $builder->qualifyColumn('tenant_id'),
                    $context->id()
                );
            }
        });

        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);

            if ($context->has()) {
                $model->tenant_id = $context->id();
            }

            if (empty($model->tenant_id)) {
                throw new RuntimeException('tenant_id kosong: context tenant belum terisi.');
            }
        });
    }
}
