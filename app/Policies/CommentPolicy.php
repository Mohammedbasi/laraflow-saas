<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Spatie\Permission\PermissionRegistrar;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Listing comments under a parent.
     */
    public function viewAnyForProject(User $user, Project $project): bool
    {
        return $user->tenant_id === $project->tenant_id;
    }

    public function viewAnyForTask(User $user, Task $task): bool
    {
        return $user->tenant_id === $task->tenant_id;
    }

    /**
     * Creating comments under a parent.
     */
    public function createForProject(User $user, Project $project): bool
    {
        return $user->tenant_id === $project->tenant_id;
    }

    public function createForTask(User $user, Task $task): bool
    {
        return $user->tenant_id === $task->tenant_id;
    }

    public function update(User $user, Comment $comment): bool
    {
        if ($user->tenant_id !== $comment->tenant_id) {
            return false;
        }

        // author can update
        if ((int) $comment->user_id === (int) $user->id) {
            return true;
        }

        // tenant_admin can update any
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $this->update($user, $comment);
    }
}
