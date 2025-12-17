<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\PermissionRegistrar;

class AdminUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Ensure team context is set so getRoleNames() returns tenant roles correctly
        $teamId = $this->tenant_id ?? config('laraflow.platform_team_id', 0);
        app(PermissionRegistrar::class)->setPermissionsTeamId($teamId);

        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => (bool) $this->is_active,

            'roles' => $this->getRoleNames()->values(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
