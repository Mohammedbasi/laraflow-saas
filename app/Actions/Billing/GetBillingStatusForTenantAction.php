<?php

namespace App\Actions\Billing;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class GetBillingStatusForTenantAction
{
    public function execute(Tenant $tenant): array
    {
        $limit = (int) Config::get('billing.free_plan_user_limit', 3);
        $userCount = User::query()->where('tenant_id', $tenant->id)->count();

        $subName = Config::get('billing.subscription_name', 'default');
        $sub = $tenant->subscription($subName);
        $isPaidEffective = $tenant->isPaidEffective($subName);

        return [
            'tenant_id' => $tenant->id,
            'plan_type' => $tenant->plan_type ?? 'free',
            'free_plan_user_limit' => $limit,
            'user_count' => $userCount,
            'subscription' => [
                'exists' => (bool) $sub,
                'active' => $sub?->active() ?? false,
                'on_grace_period' => $sub?->onGracePeriod() ?? false,
                'ends_at' => optional($sub?->ends_at)->toISOString(),
                'stripe_status' => $sub?->stripe_status,
                'stripe_price' => $sub?->stripe_price,
            ],
            'is_paid_effective' => $isPaidEffective,
        ];
    }
}
