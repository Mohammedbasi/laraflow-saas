<?php

namespace App\Actions\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateTaskAction
{
    public function execute(Project $project, array $data): Task
    {
        $status = $data['status'] ?? Task::STATUS_TODO;

        $maxPosition = Task::query()
            ->where('project_id', $project->id)
            ->where('status', $status)
            ->max('position');

        $position = ($maxPosition ?: 0) + 1000;

        if (! empty($data['assignee_id'])) {
            $assignee = User::query()->whereKey($data['assignee_id'])->first();

            if (! $assignee || (int) $assignee->tenant_id !== (int) $project->tenant_id) {
                throw ValidationException::withMessages([
                    'assignee_id' => ['Assignee must belong to the same tenant.'],
                ]);
            }
        }

        return Task::create([
            'tenant_id' => $project->tenant_id,
            'project_id' => $project->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $status,
            'position' => $position,
            'due_at' => $data['due_at'] ?? null,
            'assignee_id' => $data['assignee_id'] ?? null,
        ]);
    }
}
