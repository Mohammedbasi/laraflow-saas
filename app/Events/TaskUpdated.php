<?php

namespace App\Events;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Task $task,
        public string $type = 'task.updated',
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $tenantId = (int) $this->task->tenant_id;

        return [new PrivateChannel("App.Models.Tenant.{$tenantId}")];
    }

    public function broadcastAs(): string
    {
        // Client listens to: .TaskUpdated (default) OR custom name below
        return 'TaskUpdated';
    }

    public function broadcastWith(): array
    {
        // payload consistent with API Resources
        $taskArray = (new TaskResource($this->task))->resolve();

        return [
            'type' => $this->type,
            'tenant_id' => (int) $this->task->tenant_id,
            'project_id' => (int) $this->task->project_id,
            'task' => $taskArray,
        ];
    }
}
