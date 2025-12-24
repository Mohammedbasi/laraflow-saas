<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\PermissionRegistrar;

class AttachmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Media $media): bool
    {
        $model = $media->model; // Project or Task
        $tenantId = $model?->tenant_id;

        return $tenantId && (int) $user->tenant_id === (int) $tenantId;
    }

    public function viewProject(User $user, Project $project): bool
    {
        return $user->tenant_id === $project->tenant_id;
    }

    public function viewTask(User $user, Task $task): bool
    {
        return $user->tenant_id === $task->tenant_id;
    }

    public function uploadToProject(User $user, Project $project): bool
    {
        return $this->isTenantAdminOn($user, $project->tenant_id);
    }

    public function uploadToTask(User $user, Task $task): bool
    {
        return $this->isTenantAdminOn($user, $task->tenant_id);
    }

    public function delete(User $user, Media $media): bool
    {
        // media is attached to Project or Task
        $model = $media->model;

        $tenantId = $model?->tenant_id;

        if (! $tenantId || $user->tenant_id !== $tenantId) {
            return false;
        }

        return $this->isTenantAdminOn($user, $tenantId);
    }

    private function isTenantAdminOn(User $user, int $tenantId): bool
    {
        if ((int) $user->tenant_id !== (int) $tenantId) {
            return false;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }
}
