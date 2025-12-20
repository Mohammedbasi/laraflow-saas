<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Tenant;
use App\Observers\ProjectObserver;
use App\Support\Tenancy\TenantManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantManager::class, fn () => new TenantManager);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Tenant::class);
        
        Project::observe(ProjectObserver::class);
        RateLimiter::for('auth-login', function (Request $request) {
            // Per IP + email to reduce brute-force
            $key = strtolower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perHour(10)->by($request->ip());
        });

        RateLimiter::for('invitations', function (Request $request) {
            // per user (or per tenant) to prevent spam/invite abuse
            $userId = $request->user()?->id ?? 'guest';

            return Limit::perMinute(1)->by('invites:'.$userId);
        });
    }
}
