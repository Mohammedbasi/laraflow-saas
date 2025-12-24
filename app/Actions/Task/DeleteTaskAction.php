<?php

namespace App\Actions\Task;

use App\Events\TaskUpdated;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class DeleteTaskAction
{
    public function execute(Task $task): void
    {
        $snapshot = $task->replicate();
        DB::transaction(function () use ($task) {
            $task->delete();
        });

        DB::afterCommit(fn () => event(new TaskUpdated($snapshot, 'task.deleted')));
    }
}
