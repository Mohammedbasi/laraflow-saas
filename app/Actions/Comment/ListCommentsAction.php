<?php

namespace App\Actions\Comment;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ListCommentsAction
{
    /**
     * @param  Model  $commentable  Project or Task
     */
    public function execute(Model $commentable): Collection
    {
        return $commentable->comments()
            ->orderBy('created_at')
            ->get();
    }
}
