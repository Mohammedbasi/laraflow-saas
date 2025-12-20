<?php

namespace App\Actions\Billing;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Support\Facades\Config;

class CreateBillingPortalAction
{
    public function __construct(private TenantManager $tenantManager) {}

    public function execute(User $user): array
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::query()->findOrFail($this->tenantManager->tenantId());

        // Cashier billing portal
        $url = $tenant->billingPortalUrl(Config::get('billing.portal_return_url'));

        return ['url' => $url];
    }
}
