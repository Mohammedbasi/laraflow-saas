<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use App\Support\Audit\AuditLogger;

class DeleteCommentAction
{
    public function execute(Comment $comment): void
    {
        if (class_exists(AuditLogger::class)) {
            app(AuditLogger::class)->log(
                tenantId: $comment->tenant_id,
                causerId: auth()->id(),
                subject: $comment,
                description: 'comment_deleted',
                meta: [
                    'commentable_type' => $comment->commentable_type,
                    'commentable_id' => $comment->commentable_id,
                ],
                impersonatedByUserId: optional(auth()->user()?->currentAccessToken())->impersonated_by_user_id
            );
        }

        $comment->delete();
    }
}
