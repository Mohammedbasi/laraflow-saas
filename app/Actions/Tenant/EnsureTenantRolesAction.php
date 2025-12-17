<?php

namespace App\Actions\Tenant;

use App\Actions\Auth\EnsureRolesAction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EnsureTenantRolesAction
{
    public function execute(int $tenantId): void
    {
        // // Tell Spatie we are operating in this tenant context (teams mode)
        // app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        // $guard = config('permission.defaults.guard', 'web');

        // Role::findOrCreate('tenant_admin', $guard);
        // Role::findOrCreate('member', $guard);
        app(EnsureRolesAction::class)->ensureTenant($tenantId);

    }
}
