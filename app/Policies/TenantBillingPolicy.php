<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Spatie\Permission\PermissionRegistrar;

class TenantBillingPolicy
{

    public function billing(User $user, Tenant $tenant): bool
    {

        if ($user->tenant_id !== $tenant->id) {
            return false;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->hasRole('tenant_admin');
    }
}
