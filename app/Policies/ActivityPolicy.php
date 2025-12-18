<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Spatie\Permission\PermissionRegistrar;

class ActivityPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user): bool
    {
        // super admin always allowed
        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        // tenant admins only
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }
}
