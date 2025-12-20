<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Actions\Billing\CreateBillingPortalAction;
use App\Actions\Billing\CreateCheckoutSessionAction;
use App\Actions\Billing\GetBillingStatusAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function status(Request $request, GetBillingStatusAction $action)
    {
        $user = $request->user();
        $tenant = Tenant::findOrFail($user->tenant_id);

        // tenant admins only
        $this->authorize('billing', $tenant);

        return response()->json([
            'data' => $action->execute($user),
        ]);
    }

    public function checkout(Request $request, CreateCheckoutSessionAction $action)
    {
        $user = $request->user();
        $tenant = Tenant::findOrFail($user->tenant_id);

        // tenant admins only
        $this->authorize('billing', $tenant);

        $result = $action->execute($user);

        return response()->json(['data' => $result], 201);
    }

    public function portal(Request $request, CreateBillingPortalAction $action)
    {
        $user = $request->user();
        $tenant = Tenant::findOrFail($user->tenant_id);

        $this->authorize('billing', $tenant);

        $result = $action->execute($user);

        return response()->json(['data' => $result]);
    }
}
