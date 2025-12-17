<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminTenantResource extends JsonResource
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
            'name' => $this->name,
            'domain' => $this->domain,
            'plan_type' => $this->plan_type,
            'is_suspended' => (bool) $this->is_suspended,
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner?->id,
                'email' => $this->owner?->email,
                'name' => $this->owner?->name,
            ]),
            'users_count' => $this->when(isset($this->users_count), $this->users_count),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
