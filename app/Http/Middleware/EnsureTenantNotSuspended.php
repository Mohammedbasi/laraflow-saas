<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantNotSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // Super admin bypass (check under platform team)
        app(PermissionRegistrar::class)->setPermissionsTeamId(config('laraflow.platform_team_id', 0));
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        if (! $user->tenant_id) {
            return response()->json(['message' => 'Tenant context missing.'], 403);
        }

        $tenant = Tenant::withoutGlobalScopes()->find($user->tenant_id);

        if ($tenant?->is_suspended) {
            return response()->json(['message' => 'Tenant is suspended.'], 403);
        }

        return $next($request);
    }
}
