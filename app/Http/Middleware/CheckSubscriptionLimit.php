<?php

namespace App\Http\Middleware;

use App\Actions\Billing\EnforceTenantUserLimitAction;
use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimit
{
    public function __construct(
        private TenantManager $tenantManager,
        private EnforceTenantUserLimitAction $enforceAction,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->tenantManager->isSuperAdmin()) {
            return $next($request);
        }

        $tenantId = $this->tenantManager->tenantId();
        if (! $tenantId) {
            return $next($request);
        }

        $this->enforceAction->execute($tenantId);

        return $next($request);
    }
}
