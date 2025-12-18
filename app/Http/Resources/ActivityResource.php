<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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

            'causer_id' => $this->causer_id,
            'impersonated_by_user_id' => $this->impersonated_by_user_id,

            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,

            'description' => $this->description,
            'meta' => $this->meta,

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
