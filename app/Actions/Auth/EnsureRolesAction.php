<?php

namespace App\Actions\Auth;

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EnsureRolesAction
{
    public function ensureGlobal(): void
    {
        $platformTeamId = config('laraflow.platform_team_id', 0);
        // Global roles must be created with team_id = null
        app(PermissionRegistrar::class)->setPermissionsTeamId($platformTeamId);

        Role::findOrCreate('super_admin', 'web');
    }

    public function ensureTenant(int $tenantId): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        Role::findOrCreate('tenant_admin', 'web');
        Role::findOrCreate('member', 'web');
    }
}
