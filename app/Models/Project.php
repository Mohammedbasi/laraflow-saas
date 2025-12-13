<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        // 'tenant_id' is handled automatically by the Trait's 'creating' hook
    ];

    // Relationship to tasks (for later use)
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
