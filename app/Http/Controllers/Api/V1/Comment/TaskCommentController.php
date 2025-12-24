<?php

namespace App\Http\Controllers\Api\V1\Comment;

use App\Actions\Comment\CreateCommentAction;
use App\Actions\Comment\ListCommentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Task;

class TaskCommentController extends Controller
{
    public function index(Task $task, ListCommentsAction $action)
    {
        $this->authorize('viewAnyForTask', [Comment::class, $task]);

        return CommentResource::collection($action->execute($task));
    }

    public function store(StoreCommentRequest $request, Task $task, CreateCommentAction $action)
    {
        $this->authorize('createForTask', [Comment::class, $task]);

        $comment = $action->execute(
            commentable: $task,
            tenantId: $task->tenant_id,
            userId: $request->user()->id,
            body: $request->validated()['body']
        );

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
