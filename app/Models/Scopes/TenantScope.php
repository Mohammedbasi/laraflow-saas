<?php

namespace App\Models\Scopes;

use App\Support\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = Auth::user();

        // Super admin bypass (must check under platform team context)
        if ($user) {
            app(PermissionRegistrar::class)->setPermissionsTeamId(
                config('laraflow.platform_team_id', 0)
            );

            if ($user->hasRole('super_admin')) {
                return;
            }
        }

        $tenancy = app(TenantManager::class);

        // fallback: if tenant not set but user exists
        if (! $tenancy->hasTenant() && $user?->tenant_id) {
            $tenancy->setTenantId($user->tenant_id);
        }

        if (! $tenancy->hasTenant()) {
            return;
        }

        $builder->where('tenant_id', $tenancy->tenantId());
    }
}
