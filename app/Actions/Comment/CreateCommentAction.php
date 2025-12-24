<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

class CreateCommentAction
{
    /**
     * @param  Model  $commentable  Project or Task
     */
    public function execute(Model $commentable, int $tenantId, int $userId, string $body): Comment
    {
        return Comment::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'body' => $body,
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
        ]);
    }
}
