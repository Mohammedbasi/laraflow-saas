<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Spatie\Permission\PermissionRegistrar;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user, Project $project): bool
    {
        return $user->tenant_id === $project->tenant_id;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->tenant_id === $task->tenant_id;
    }

    public function create(User $user, Project $project): bool
    {
        if ($user->tenant_id !== $project->tenant_id) {
            return false;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->tenant_id !== $task->tenant_id) {
            return false;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}
