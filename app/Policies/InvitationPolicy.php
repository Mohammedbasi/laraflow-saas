<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Spatie\Permission\PermissionRegistrar;

class InvitationPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        // Ensure tenant team context
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }
}
