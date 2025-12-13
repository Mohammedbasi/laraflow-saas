<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'order',
        'assigned_to_user_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
