<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'project_id' => $this->project_id,

            'title' => $this->title,
            'description' => $this->description,

            'status' => $this->status,
            'position' => $this->position,

            'due_at' => optional($this->due_at)->toISOString(),
            'assignee_id' => $this->assignee_id,

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
