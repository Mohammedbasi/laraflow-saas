<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tenant;
use App\Policies\ActivityPolicy;
use App\Policies\AttachmentPolicy;
use App\Policies\CommentPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TenantBillingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Invitation::class => InvitationPolicy::class,
        Activity::class => ActivityPolicy::class,
        Tenant::class => TenantBillingPolicy::class,
        Task::class => TaskPolicy::class,
        Comment::class => CommentPolicy::class,
        Media::class => AttachmentPolicy::class,
    ];
}
