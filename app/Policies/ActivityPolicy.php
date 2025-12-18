<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {

        // super admin bypass
        if (app(TenantManager::class)->isSuperAdmin()) {
            return true;
        }

        if (! $user->tenant_id) {
            return false;
        }

        // Force team context for tenant roles
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        // Flush cached relations to avoid "platform team 0" pollution
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        Log::info('ActivityPolicy check', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'is_super_admin' => app(TenantManager::class)->isSuperAdmin(),
            'team_id' => app(PermissionRegistrar::class)->getPermissionsTeamId(),
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        return $user->hasRole('tenant_admin');
    }
}
