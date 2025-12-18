<?php

namespace App\Observers;

use App\Models\Project;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $this->log($project, 'Project created');
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        $this->log($project, 'Project updated', [
            'changed' => array_keys($project->getChanges()),
        ]);
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        $this->log($project, 'Project deleted');
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        //
    }

    private function log(Project $project, string $description, array $meta = []): void
    {
        $user = Auth::user();
        $token = $user?->currentAccessToken();

        app(AuditLogger::class)->log(
            tenantId: $project->tenant_id,
            causerId: $user?->id,
            impersonatedByUserId: $token?->impersonated_by_user_id,
            subject: $project,
            description: $description,
            meta: $meta
        );
    }
}
