<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUserRole extends Model
{
    protected $table = 'tenant_user_roles';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
    ];
}
