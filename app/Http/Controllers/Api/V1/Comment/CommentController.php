<?php

namespace App\Http\Controllers\Api\V1\Comment;

use App\Actions\Comment\DeleteCommentAction;
use App\Actions\Comment\UpdateCommentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;

class CommentController extends Controller
{
    public function update(UpdateCommentRequest $request, Comment $comment, UpdateCommentAction $action)
    {
        $this->authorize('update', $comment);

        $comment = $action->execute($comment, $request->validated()['body']);

        return new CommentResource($comment);
    }

    public function destroy(Comment $comment, DeleteCommentAction $action)
    {
        $this->authorize('delete', $comment);

        $action->execute($comment);

        return response()->noContent();
    }
}
