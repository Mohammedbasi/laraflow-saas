<?php

namespace App\Actions\Task;

use App\Models\Project;
use App\Models\Task;

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
