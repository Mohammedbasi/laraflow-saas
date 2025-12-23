<?php

namespace App\Actions\Task;

use App\Models\Task;

class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        $task->fill($data);
        $task->save();

        return $task->refresh();
    }
}
