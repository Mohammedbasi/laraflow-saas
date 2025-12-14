<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Support\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * The "booted" method of the model.
     * This is where we attach the Global Scope.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        // Automatically set tenant_id when creating a new record
        static::creating(function ($model) {
            $tenancy = app(TenantManager::class);

            if ($tenancy->hasTenant() && empty($model->tenant_id)) {
                $model->tenant_id = $tenancy->tenantId();
            }

        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
