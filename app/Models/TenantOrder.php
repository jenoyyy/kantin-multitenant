<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    use BelongsToTenant;
}
