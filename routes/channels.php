<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Tenant.{id}', function ($user, $id) {

    // Strict tenant membership check (prevents cross-tenant subscription)
    if ((int) $user->tenant_id !== (int) $id) {
        return false;
    }

    if (! $user->is_active) {
        return false;
    }

    if ($user->tenant?->is_suspended) {
        return false;
    }

     return true;
});
