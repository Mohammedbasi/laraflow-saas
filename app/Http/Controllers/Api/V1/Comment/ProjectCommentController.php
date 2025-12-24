<?php

namespace App\Http\Controllers\Api\V1\Comment;

use App\Actions\Comment\CreateCommentAction;
use App\Actions\Comment\ListCommentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;

class ProjectCommentController extends Controller
{
    public function index(Project $project, ListCommentsAction $action)
    {
        $this->authorize('viewAnyForProject', [Comment::class, $project]);

        return CommentResource::collection($action->execute($project));
    }

    public function store(StoreCommentRequest $request, Project $project, CreateCommentAction $action)
    {
        $this->authorize('createForProject', [Comment::class, $project]);

        $comment = $action->execute(
            commentable: $project,
            tenantId: $project->tenant_id,
            userId: $request->user()->id,
            body: $request->validated()['body']
        );

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
