<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // allow API token auth (Sanctum) and tenant context for channel auth.
        // Broadcast::routes([
        //     'middleware' => ['api', 'auth:sanctum', 'tenant.context'],
        // ]);

        require base_path('routes/channels.php');
    }
}
