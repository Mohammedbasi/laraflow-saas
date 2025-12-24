<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Tenant.{id}', function ($user, int $id) {
    return (int) $user->tenant_id === (int) $id;
});
