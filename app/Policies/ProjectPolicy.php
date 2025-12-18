<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Spatie\Permission\PermissionRegistrar;

class ProjectPolicy
{
    /**
     * Anyone in the tenant can view projects (we’ll refine later if needed).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return true;
    }

    /**
     * Only tenant_admin can create/update/delete projects.
     */
    public function create(User $user): bool
    {

        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        // IMPORTANT: set tenant team context before role check
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }

    public function update(User $user, Project $project): bool
    {
        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }

    public function delete(User $user, Project $project): bool
    {
        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }
}
