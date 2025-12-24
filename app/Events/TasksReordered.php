<?php

namespace App\Events;

use App\Http\Resources\TaskResource;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TasksReordered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $tenantId,
        public int $projectId,
        public array $tasks,
        public array $moves = [],
    ) {
        //
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("App.Models.Tenant.{$this->tenantId}")];
    }

    public function broadcastAs(): string
    {
        return 'TasksReordered';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'tasks.reordered',
            'tenant_id' => $this->tenantId,
            'project_id' => $this->projectId,
            'moves' => $this->moves,
            'tasks' => TaskResource::collection(collect($this->tasks))->resolve(),
        ];
    }
}
