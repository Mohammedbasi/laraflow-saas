<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'causer_id',
        'impersonated_by_user_id',
        'subject_type',
        'subject_id',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
