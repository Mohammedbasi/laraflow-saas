<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Auth\EnsureRolesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetUserRolesRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $tenantId = $request->integer('tenant_id');

        $users = User::withoutGlobalScopes()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($q, fn ($query) => $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            }))
            ->orderByDesc('id')
            ->paginate(15);

        return AdminUserResource::collection($users);
    }

    public function show(User $user)
    {
        $user = User::withoutGlobalScopes()->findOrFail($user->id);

        return new AdminUserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user = User::withoutGlobalScopes()->findOrFail($user->id);

        // Avoid editing super admin users via this endpoint (safety)
        $this->denyIfSuperAdmin($user);

        $user->update($request->validated());

        return new AdminUserResource($user);
    }

    public function setRoles(SetUserRolesRequest $request, User $user, EnsureRolesAction $rolesAction)
    {
        $user = User::withoutGlobalScopes()->findOrFail($user->id);
        $this->denyIfSuperAdmin($user);

        if (! $user->tenant_id) {
            return response()->json(['message' => 'Cannot set tenant roles for user without tenant.'], 422);
        }

        // Ensure tenant roles exist
        $rolesAction->ensureTenant($user->tenant_id);

        // IMPORTANT: set team context to that tenant before syncing roles
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        $user->syncRoles($request->validated()['roles']);

        return new AdminUserResource($user->fresh());
    }

    public function deactivate(User $user)
    {
        $user = User::withoutGlobalScopes()->findOrFail($user->id);
        $this->denyIfSuperAdmin($user);

        $user->update(['is_active' => false]);

        // Optional: revoke tokens so they get kicked out immediately
        $user->tokens()->delete();

        return response()->json(['message' => 'User deactivated.']);
    }

    public function activate(User $user)
    {
        $user = User::withoutGlobalScopes()->findOrFail($user->id);
        $this->denyIfSuperAdmin($user);

        $user->update(['is_active' => true]);

        return response()->json(['message' => 'User activated.']);
    }

    private function denyIfSuperAdmin(User $user): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(config('laraflow.platform_team_id', 0));

        if ($user->hasRole('super_admin')) {
            abort(403, 'Cannot modify a super admin with this endpoint.');
        }
    }
}
