<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class WeeklyReport extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'week_start',
        'week_end',
        'status',
        'stats',
        'pdf_disk',
        'pdf_path',
        'last_error',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'stats' => 'array',
    ];
}
