<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function canteen(): BelongsTo
    {
        return $this->belongsTo(Canteen::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function tenantOrders(): HasMany
    {
        return $this->hasMany(TenantOrder::class);
    }

    public function commissionSchemes(): HasMany
    {
        return $this->hasMany(CommissionScheme::class);
    }

    public function tenantRoles(): HasMany
    {
        return $this->hasMany(TenantUserRole::class);
    }

        public function bankAccounts(): HasMany
    {
        return $this->hasMany(TenantBankAccount::class);
    }
    
    public function balance(): HasOne
    {
        return $this->hasOne(TenantBalance::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}