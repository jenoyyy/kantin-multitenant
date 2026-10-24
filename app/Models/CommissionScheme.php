<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Versi tarif komisi tenant dengan tanggal efektif.
 * Periode dibaca [valid_from, valid_to); valid_to NULL = versi aktif.
 */
class CommissionScheme extends Model
{
    protected $fillable = ['tenant_id', 'commission_rate', 'valid_from', 'valid_to'];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:4',
            'valid_from' => 'datetime',
            'valid_to' => 'datetime',
        ];
    }

    /**
     * Skema yang berlaku pada instan tertentu. valid_to adalah instan pertama
     * skema TIDAK berlaku lagi, yaitu valid_from versi penggantinya.
     */
    public function scopeEffectiveAt(Builder $query, CarbonInterface $instant): void
    {
        $query->where('valid_from', '<=', $instant)
            ->where(function (Builder $q) use ($instant): void {
                $q->whereNull('valid_to')->orWhere('valid_to', '>', $instant);
            });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
