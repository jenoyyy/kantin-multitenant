<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Saldo tenant (tabel balances). Satu baris per tenant, mulai dari 0.
 */
class TenantBalance extends Model
{
    protected $table = 'balances';

    protected $fillable = ['tenant_id', 'amount'];
}
