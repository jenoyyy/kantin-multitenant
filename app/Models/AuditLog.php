<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catatan audit aksi administrasi (siapa, melakukan apa, pada apa).
 */
class AuditLog extends Model
{
    protected $fillable = [
        'actor_type', 'actor_id', 'action',
        'target_type', 'target_id', 'changes',
    ];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }
}