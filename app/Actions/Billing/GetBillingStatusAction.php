<?php

namespace App\Actions\Billing;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Support\Facades\Config;

class GetBillingStatusAction
{
    public function __construct(private TenantManager $tenantManager) {}

    public function execute(User $user): array
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::query()->findOrFail($this->tenantManager->tenantId());

        $limit = (int) Config::get('billing.free_plan_user_limit', 3);
        $userCount = $tenant->users()->count();

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
