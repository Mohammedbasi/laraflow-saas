<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class InvitationPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        return $user->hasRole('tenant_admin');
    }
}
