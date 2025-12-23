<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Actions\Task\ReorderTasksAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\ReorderTasksRequest;
use App\Models\Project;
use App\Models\Task;

class TaskReorderController extends Controller
{
    public function __invoke(ReorderTasksRequest $request, Project $project, ReorderTasksAction $action)
    {
        $this->authorize('reorder', [Task::class, $project]);

        $action->execute($project, $request->validated()['moves'], $request->user());

        return response()->json(['data' => ['ok' => true]]);
    }
}
