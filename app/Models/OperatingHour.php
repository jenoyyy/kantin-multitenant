<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jam operasional tenant per hari (0 = Minggu ... 6 = Sabtu), waktu lokal kantin (WIB).
 * Tenant tanpa baris jam dianggap buka sepanjang waktu (belum dikonfigurasi).
 */
class OperatingHour extends Model
{
    protected $fillable = ['tenant_id', 'day_of_week', 'open_time', 'close_time', 'is_closed'];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_closed' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
