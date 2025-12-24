<?php

namespace App\Actions\Comment;

use App\Models\Comment;

class UpdateCommentAction
{
    public function execute(Comment $comment, string $body): Comment
    {
        $comment->update(['body' => $body]);

        return $comment->refresh();
    }
}
