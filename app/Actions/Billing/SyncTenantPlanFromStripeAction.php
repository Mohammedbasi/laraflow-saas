<?php

namespace App\Actions\Billing;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

class SyncTenantPlanFromStripeAction
{
    public function executeByStripeCustomerId(string $stripeCustomerId): void
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->where('stripe_id', $stripeCustomerId)->first();

        if (! $tenant) {
            return;
        }

        $subName = Config::get('billing.subscription_name', 'default');
        $sub = $tenant->subscription($subName);

        // If subscription exists and active or on grace period => paid
        if ($sub && ($sub->active() || $sub->onGracePeriod())) {
            if ($tenant->plan_type !== 'paid') {
                $tenant->forceFill(['plan_type' => 'paid'])->save();
            }

            return;
        }

        // Otherwise, free
        if ($tenant->plan_type !== 'free') {
            $tenant->forceFill(['plan_type' => 'free'])->save();
        }
    }
}
