<?php

namespace App\Providers;

use App\Models\Project;
use App\Observers\ProjectObserver;
use App\Support\Tenancy\TenantManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantManager::class, fn () => new TenantManager());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Project::observe(ProjectObserver::class);
    }
}
