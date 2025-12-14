<?php

namespace App\Models\Scopes;

use App\Support\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $tenancy = app(TenantManager::class);

        // If tenant is not set, do nothing (for super-admin, console, etc.)
        if (! $tenancy->hasTenant()) {
            return;
        }

        $builder->where('tenant_id', $tenancy->tenantId());
    }
}
