<?php

namespace App\Actions\Task;

use App\Events\TaskUpdated;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        DB::transaction(function () use ($task, $data) {
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
        });

        // Broadcast AFTER commit so clients never get rolled-back state.
        DB::afterCommit(function () use ($task) {
            event(new TaskUpdated($task->fresh(), 'task.updated'));
        });

        return $task->refresh();
    }
}
