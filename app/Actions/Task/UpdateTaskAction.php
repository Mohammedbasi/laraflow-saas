<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        $task->fill($data);

        if (array_key_exists('assignee_id', $data) && ! empty($data['assignee_id'])) {
            $assignee = User::query()->whereKey($data['assignee_id'])->first();

            if (! $assignee || (int) $assignee->tenant_id !== (int) $task->tenant_id) {
                throw ValidationException::withMessages([
                    'assignee_id' => ['Assignee must belong to the same tenant.'],
                ]);
            }
        }

        $task->save();

        return $task->refresh();
    }
}
