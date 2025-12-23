<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Billing\CreateBillingPortalForTenantAction;
use App\Actions\Billing\GetBillingStatusForTenantAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AdminBillingController extends Controller
{
    public function status(Request $request, Tenant $tenant, GetBillingStatusForTenantAction $action)
    {
        
        return response()->json(['data' => $action->execute($tenant)]);
    }

    public function portal(Request $request, Tenant $tenant, CreateBillingPortalForTenantAction $action)
    {
        return response()->json(['data' => $action->execute($tenant)]);
    }
}
