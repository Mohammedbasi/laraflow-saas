<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class SetTenantFromAuthenticatedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $platformTeamId = config('laraflow.platform_team_id', 0);

        $tenancy = app(TenantManager::class);

        // default
        $tenancy->setSuperAdmin(false);

        if (! $user) {
            $tenancy->setTenantId(null);

            return $next($request);
        }

        // Check super_admin in platform team context ONCE
        app(PermissionRegistrar::class)->setPermissionsTeamId($platformTeamId);

        if ($user->hasRole('super_admin')) {
            $tenancy->setSuperAdmin(true);
            $tenancy->setTenantId(null);

            // keep registrar on platform team if you want
            app(PermissionRegistrar::class)->setPermissionsTeamId($platformTeamId);

            return $next($request);
        }

        // Normal tenant user
        $tenancy->setTenantId($user->tenant_id);

        if ($user->tenant_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
        }

        return $next($request);
    }
}
