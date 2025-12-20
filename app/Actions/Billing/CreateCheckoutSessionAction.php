<?php

namespace App\Actions\Billing;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Support\Facades\Config;

class CreateCheckoutSessionAction
{
    public function __construct(private TenantManager $tenantManager) {}

    public function execute(User $user): array
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::query()->findOrFail($this->tenantManager->tenantId());

        $subName = Config::get('billing.subscription_name', 'default');
        $priceId = Config::get('billing.pro_price_id');

        abort_if(! $priceId, 500, 'Stripe price id not configured.');

        // If already active / effective paid, don’t create another checkout
        if ($tenant->isPaidEffective($subName)) {
            return [
                'already_subscribed' => true,
                'checkout_url' => null,
            ];
        }

        // Cashier checkout flow
        $checkout = $tenant->newSubscription($subName, $priceId)->checkout([
            'success_url' => Config::get('billing.checkout_success_url').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => Config::get('billing.checkout_cancel_url'),
        ]);

        return [
            'already_subscribed' => false,
            'checkout_url' => $checkout->url,
            'session_id' => $checkout->id,
        ];
    }
}
