<?php

namespace App\Actions\Billing;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

class CreateBillingPortalForTenantAction
{
    public function execute(Tenant $tenant): array
    {
        $url = $tenant->billingPortalUrl(Config::get('billing.portal_return_url'));

        return ['url' => $url];
    }
}
