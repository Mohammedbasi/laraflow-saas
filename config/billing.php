<?php

return [
    'subscription_name' => env('BILLING_SUBSCRIPTION_NAME', 'default'),

    // This is a Stripe “Price” ID (recurring).
    'pro_price_id' => env('STRIPE_PRO_PRICE_ID', 'price_xxx'),

    // API URLs frontend uses.
    'checkout_success_url' => env('BILLING_CHECKOUT_SUCCESS_URL', env('APP_URL').'/billing/success'),
    'checkout_cancel_url' => env('BILLING_CHECKOUT_CANCEL_URL', env('APP_URL').'/billing/cancel'),
    'portal_return_url' => env('BILLING_PORTAL_RETURN_URL', env('APP_URL').'/billing'),

    'free_plan_user_limit' => (int) env('FREE_PLAN_USER_LIMIT', 3),
];
