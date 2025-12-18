<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        $impersonatedById = $token?->impersonated_by_user_id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'tenant_id' => $this->tenant_id,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),

            // current auth context
            'auth_context' => [
                'token_id' => $token?->id,
                'abilities' => $token?->abilities ?? [],
                'is_impersonating' => (bool) $impersonatedById,
                'impersonated_by_user_id' => $impersonatedById,
            ],
        ];
    }
}
