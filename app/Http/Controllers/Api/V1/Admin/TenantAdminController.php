<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminTenantResource;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $tenants = Tenant::withoutGlobalScopes()
            ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%")
                ->orWhere('domain', 'like', "%{$q}%"))
            ->withCount(['users'])
            ->orderByDesc('id')
            ->paginate(15);

        return AdminTenantResource::collection($tenants);
    }

    public function show(Tenant $tenant)
    {
        $tenant = Tenant::withoutGlobalScopes()
            ->with(['owner'])
            ->withCount(['users'])
            ->findOrFail($tenant->id);

        return new AdminTenantResource($tenant);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain,'.$tenant->id],
            'plan_type' => ['sometimes', 'in:free,paid'],
        ]);

        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenant->id);
        $tenant->update($data);

        return new AdminTenantResource($tenant);
    }

    public function suspend(Tenant $tenant)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenant->id);
        $tenant->update(['is_suspended' => true]);

        return response()->json(['message' => 'Tenant suspended.']);
    }

    public function activate(Tenant $tenant)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenant->id);
        $tenant->update(['is_suspended' => false]);

        return response()->json(['message' => 'Tenant activated.']);
    }
}
