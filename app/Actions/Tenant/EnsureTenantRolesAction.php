<?php

namespace App\Actions\Tenant;

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EnsureTenantRolesAction
{
    public function execute(int $tenantId): void
    {
        // Tell Spatie we are operating in this tenant context (teams mode)
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $guard = config('permission.defaults.guard', 'web');

        Role::findOrCreate('tenant_admin', $guard);
        Role::findOrCreate('member', $guard);

        // super_admin is usually global and not tenant-scoped.
        // We'll implement it later with a separate approach.
    }
}
