<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nomor rekening disimpan terenkripsi (cast encrypted); last4 untuk tampilan.
 * tenant_id dan nomor rekening tidak mass-assignable (diisi service tepercaya).
 */
class TenantBankAccount extends Model
{
    protected $table = 'bank_accounts';

    protected $fillable = ['bank_name', 'account_holder_name', 'account_number_last4', 'status', 'is_primary'];

    protected function casts(): array
    {
        return [
            'account_number_encrypted' => 'encrypted',
            'is_primary' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}