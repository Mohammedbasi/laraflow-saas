<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_TODO = 'todo';

    public const STATUS_DOING = 'doing';

    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_TODO,
        self::STATUS_DOING,
        self::STATUS_DONE,
    ];

    protected $fillable = [
        'tenant_id',
        'project_id',
        'title',
        'description',
        'status',
        'position',
        'due_at',
        'assignee_id',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function scopeForProject(Builder $q, int $projectId): Builder
    {
        return $q->where('project_id', $projectId);
    }
}
