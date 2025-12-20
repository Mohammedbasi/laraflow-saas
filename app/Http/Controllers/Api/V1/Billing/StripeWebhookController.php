<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Actions\Billing\SyncTenantPlanFromStripeAction;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    public function __construct(private SyncTenantPlanFromStripeAction $syncAction)
    {
        parent::__construct();
    }

    public function handleWebhook(Request $request): Response
    {
        // Let Cashier do its normal work (update subscriptions, etc.)
        $response = parent::handleWebhook($request);

        // Then sync plan_type based on the updated subscription state.
        $payload = $request->all();

        $customerId = data_get($payload, 'data.object.customer');
        if (is_string($customerId) && $customerId !== '') {
            $this->syncAction->executeByStripeCustomerId($customerId);
        }

        return $response;
    }
}
