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

        $registrar = app(PermissionRegistrar::class);

        // 1) Check super_admin under PLATFORM team context
        $registrar->setPermissionsTeamId($platformTeamId);

        // IMPORTANT: clear cached relations so hasRole checks the correct team context
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        if ($user->hasRole('super_admin')) {
            $tenancy->setSuperAdmin(true);
            $tenancy->setTenantId(null);

            // keep registrar on platform team
            $registrar->setPermissionsTeamId($platformTeamId);

            return $next($request);
        }

        // Normal tenant user
        $tenancy->setTenantId($user->tenant_id);

        if ($user->tenant_id) {
            $registrar->setPermissionsTeamId($user->tenant_id);
            
            // IMPORTANT: clear cached relations again so tenant role checks work
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }

        return $next($request);
    }
}
