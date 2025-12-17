<?php

namespace App\Providers;

use App\Models\Invitation;
use App\Models\Project;
use App\Policies\InvitationPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Invitation::class => InvitationPolicy::class,
    ];
}
