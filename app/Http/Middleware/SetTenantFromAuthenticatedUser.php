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

        // 1) Check super_admin in platform team context
        app(PermissionRegistrar::class)->setPermissionsTeamId($platformTeamId);

        if ($user && $user->hasRole('super_admin')) {
            // Super admin: no tenant scoping
            app(TenantManager::class)->setTenantId(null);

            return $next($request);
        }

        // 2) Normal tenant user: set tenant/team context
        if ($user?->tenant_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
            app(TenantManager::class)->setTenantId($user->tenant_id);
        }

        return $next($request);
    }
}
