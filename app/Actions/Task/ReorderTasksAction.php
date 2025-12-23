<?php

namespace App\Actions\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderTasksAction
{
    public function execute(Project $project, array $moves, ?User $actor = null): void
    {
        DB::transaction(function () use ($project, $moves, $actor) {

            $taskIds = collect($moves)->pluck('task_id')->unique()->values();

            // Load tasks and verify they belong to this project
            $tasks = Task::query()
                ->whereIn('id', $taskIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($tasks->count() !== $taskIds->count()) {
                throw ValidationException::withMessages([
                    'moves' => ['One or more tasks could not be found.'],
                ]);
            }

            // Prevent cross-project updates
            $invalid = $tasks->first(fn (Task $t) => (int) $t->project_id !== (int) $project->id);
            if ($invalid) {
                throw ValidationException::withMessages([
                    'moves' => ['One or more tasks do not belong to this project.'],
                ]);
            }

            foreach ($moves as $move) {
                /** @var Task $task */
                $task = $tasks[(int) $move['task_id']];

                $fromStatus = $task->status;
                $fromPos = $task->position;

                $task->status = $move['status'];
                $task->position = (int) $move['position'];
                $task->save();

                // Optional: log only if something changed
                if (class_exists(AuditLogger::class) && $actor && ($fromStatus !== $task->status || $fromPos !== $task->position)) {
                    app(AuditLogger::class)->log(
                        tenantId: $project->tenant_id,
                        causerId: $actor->id,
                        subject: $task,
                        description: 'task_reordered',
                        meta: [
                            'project_id' => $project->id,
                            'from_status' => $fromStatus,
                            'to_status' => $task->status,
                            'from_position' => $fromPos,
                            'to_position' => $task->position,
                        ],
                        impersonatedByUserId: optional($actor->currentAccessToken())->impersonated_by_user_id
                    );
                }
            }
        });
    }
}
