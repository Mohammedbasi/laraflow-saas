<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
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
        $this->setTeam($user);

        return $user->hasRole('tenant_admin');
    }

    public function update(User $user, Project $project): bool
    {
        $this->setTeam($user);

        return $user->hasRole('tenant_admin');
    }

    public function delete(User $user, Project $project): bool
    {
        $this->setTeam($user);

        return $user->hasRole('tenant_admin');
    }


    // Because we enabled Spatie “teams” and roles are tenant-scoped.
    private function setTeam(User $user): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
    }
}
