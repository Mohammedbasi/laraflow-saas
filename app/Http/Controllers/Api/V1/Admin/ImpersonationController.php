<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\PermissionRegistrar;

class ImpersonationController extends Controller
{
    /**
     * Super-admin starts impersonation: returns a token for the target user.
     */
    public function start(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'ttl_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'], // up to 24h
        ]);

        $superAdmin = $request->user();

        // Ensure this check is done under platform team context (teams mode)
        app(PermissionRegistrar::class)->setPermissionsTeamId(config('laraflow.platform_team_id', 0));
        if (! $superAdmin->hasRole('super_admin')) {
            abort(403);
        }

        $target = User::withoutGlobalScopes()->findOrFail($data['user_id']);

        // Do not impersonate another super admin
        app(PermissionRegistrar::class)->setPermissionsTeamId(config('laraflow.platform_team_id', 0));
        if ($target->hasRole('super_admin')) {
            throw ValidationException::withMessages([
                'user_id' => ['You cannot impersonate a super admin.'],
            ]);
        }

        // Optional: deny impersonating inactive users
        if (! $target->is_active) {
            throw ValidationException::withMessages([
                'user_id' => ['Cannot impersonate an inactive user.'],
            ]);
        }

        $ttl = $data['ttl_minutes'] ?? 60;

        // Create token for the TARGET user
        $token = $target->createToken(
            $data['device_name'] ?? 'impersonation',
            abilities: ['*'] // simplest; we rely on policies/roles as the user
        );

        // Attach audit metadata to the token row
        /** @var PersonalAccessToken $tokenRow */
        $tokenRow = $token->accessToken;
        $tokenRow->forceFill([
            'impersonated_by_user_id' => $superAdmin->id,
            'impersonation_started_at' => now(),
            // Sanctum supports expires_at in recent versions. If yours has it, set it:
            'expires_at' => now()->addMinutes($ttl),
        ])->save();

        app(AuditLogger::class)->log(
            tenantId: $target->tenant_id,              // audit under tenant being accessed
            causerId: $target->id,                     // "acting as" user (target)
            impersonatedByUserId: $superAdmin->id,     // super admin who initiated
            subject: $target,                          // or null; but target is useful
            description: 'Impersonation started',
            meta: [
                'impersonation_token_id' => $tokenRow->id,
                'super_admin_id' => $superAdmin->id,
                'target_user_id' => $target->id,
            ]
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'impersonated_user' => [
                'id' => $target->id,
                'tenant_id' => $target->tenant_id,
                'name' => $target->name,
                'email' => $target->email,
            ],
            'impersonated_by' => [
                'id' => $superAdmin->id,
                'email' => $superAdmin->email,
            ],
            'expires_in_minutes' => $ttl,
        ], 201);
    }

    /**
     * Stop impersonation by revoking the CURRENT token if it is an impersonation token.
     * This endpoint is meant to be called using the impersonation token itself.
     */
    public function stop(Request $request)
    {
        $authUser = $request->user();
        if (! $authUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Parse token id from the Authorization header token
        $bearer = $request->bearerToken();
        if (! $bearer || ! str_contains($bearer, '|')) {
            return response()->json(['message' => 'Invalid token format.'], 422);
        }

        [$tokenId] = explode('|', $bearer, 2);

        $token = PersonalAccessToken::find($tokenId);

        if (! $token) {
            return response()->json(['message' => 'Token not found.'], 404);
        }

        if (! $token->impersonated_by_user_id) {
            return response()->json(['message' => 'Not an impersonation token.'], 422);
        }

        app(AuditLogger::class)->log(
            tenantId: $request->user()->tenant_id,
            causerId: $request->user()->id,
            impersonatedByUserId: $token->impersonated_by_user_id,
            subject: $request->user(),
            description: 'Impersonation stopped',
            meta: [
                'impersonation_token_id' => $token->id,
            ]
        );

        $token->delete();

        return response()->json(['message' => 'Impersonation stopped.']);
    }
}
