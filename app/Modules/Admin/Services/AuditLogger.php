<?php

namespace App\Modules\Admin\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Mencatat aksi administrasi ke tabel audit_logs.
 */
class AuditLogger
{
    /**
     * @param  string  $action   contoh: 'tenant.created', 'tenant.status_changed'
     * @param  Model   $target   data yang dikenai aksi (Tenant, dst.)
     * @param  array   $changes  ringkasan perubahan (jangan isi data rahasia)
     */
    public function record(string $action, Model $target, array $changes = []): AuditLog
    {
        $user = Auth::user();

        return AuditLog::create([
            'actor_type'  => $user ? 'user' : 'system',
            'actor_id'    => $user?->getKey(),
            'action'      => $action,
            'target_type' => class_basename($target),
            'target_id'   => $target->getKey(),
            'changes'     => $changes ?: null,
        ]);
    }
}