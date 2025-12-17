<?php

namespace App\Actions\Invitation;

use App\Actions\Tenant\EnsureTenantRolesAction;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class CompleteInvitationAction
{
    public function execute(
        Invitation $invitation,
        string $plainToken,
        string $name,
        string $password,
        string $deviceName = 'api'
    ): array {
        if ($invitation->isAccepted()) {
            throw ValidationException::withMessages([
                'invitation' => ['This invitation has already been accepted.'],
            ]);
        }

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'invitation' => ['This invitation has expired.'],
            ]);
        }

        if (! hash_equals($invitation->token, hash('sha256', $plainToken))) {
            throw ValidationException::withMessages([
                'token' => ['Invalid invitation token.'],
            ]);
        }

        return DB::transaction(function () use ($invitation, $name, $password, $deviceName) {

            // prevent email reuse
            if (User::query()->where('email', $invitation->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['A user with this email already exists.'],
                ]);
            }

            $user = User::create([
                'tenant_id' => $invitation->tenant_id,
                'name' => $name,
                'email' => $invitation->email,
                'password' => Hash::make($password),
            ]);

            // Tenant-scoped roles (Spatie teams)
            app(PermissionRegistrar::class)->setPermissionsTeamId($invitation->tenant_id);
            app(EnsureTenantRolesAction::class)->execute($invitation->tenant_id);

            $user->assignRole($invitation->role ?: 'member');

            $invitation->update([
                'accepted_at' => now(),
            ]);

            $token = $user->createToken($deviceName)->plainTextToken;

            return compact('user', 'token');
        });
    }
}
