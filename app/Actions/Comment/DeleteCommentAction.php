<?php

namespace App\Actions\Comment;

use App\Models\Comment;

class DeleteCommentAction
{
    public function execute(Comment $comment): void
    {
        $comment->delete();
    }
}
