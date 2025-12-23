<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Actions\Task\CreateTaskAction;
use App\Actions\Task\DeleteTaskAction;
use App\Actions\Task\ListProjectTasksAction;
use App\Actions\Task\UpdateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request, Project $project, ListProjectTasksAction $action)
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $tasks = $action->execute($project, $request->only(['status']));

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project, CreateTaskAction $action)
    {
        $this->authorize('create', [Task::class, $project]);

        $task = $action->execute($project, $request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $action)
    {
        $this->authorize('update', $task);

        $task = $action->execute($task, $request->validated());

        return new TaskResource($task);
    }

    public function destroy(Request $request, Task $task, DeleteTaskAction $action)
    {
        $this->authorize('delete', $task);

        $action->execute($task);

        return response()->noContent();
    }
}
