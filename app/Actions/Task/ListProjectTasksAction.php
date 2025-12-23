<?php

namespace App\Actions\Task;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class ListProjectTasksAction
{
    public function execute(Project $project, array $filters = []): Collection
    {
        $q = Task::query()->where('project_id', $project->id);

        if (! empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q->orderBy('status')->orderBy('position')->get();
    }
}
