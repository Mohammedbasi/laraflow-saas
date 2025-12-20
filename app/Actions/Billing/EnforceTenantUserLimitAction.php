<?php

namespace App\Actions\Billing;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class EnforceTenantUserLimitAction
{
    /**
     * Throws ValidationException if tenant cannot add another user.
     */
    public function execute(int $tenantId): void
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::query()->findOrFail($tenantId);

        $subscriptionName = Config::get('billing.subscription_name', 'default');

        // Paid (active or grace) => unlimited
        if ($tenant->isPaidEffective($subscriptionName)) {
            return;
        }

        $limit = (int) Config::get('billing.free_plan_user_limit', 3);

        $count = User::query()
            ->where('tenant_id', $tenantId)
            ->count();

        if ($count >= $limit) {
            throw ValidationException::withMessages([
                'subscription' => ["Free plan allows up to {$limit} users. Upgrade to add more users."],
            ]);
        }
    }
}
