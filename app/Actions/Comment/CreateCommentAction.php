<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use App\Support\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class CreateCommentAction
{
    /**
     * @param  Model  $commentable  Project or Task
     */
    public function execute(Model $commentable, int $tenantId, int $userId, string $body): Comment
    {
        $comment = Comment::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'body' => $body,
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
        ]);

        if (class_exists(AuditLogger::class)) {
            app(AuditLogger::class)->log(
                tenantId: $tenantId,
                causerId: $userId,
                subject: $comment,
                description: 'comment_created',
                meta: [
                    'commentable_type' => $comment->commentable_type,
                    'commentable_id' => $comment->commentable_id,
                ],
                impersonatedByUserId: optional(auth()->user()?->currentAccessToken())->impersonated_by_user_id
            );
        }

        return $comment;

    }
}
