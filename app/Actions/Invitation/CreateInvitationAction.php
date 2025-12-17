<?php

namespace App\Actions\Invitation;

use App\Models\Invitation;
use Illuminate\Support\Str;

class CreateInvitationAction
{
    public function execute(int $tenantId, int $invitedByUserId, string $email, string $role = 'member'): array
    {
        $plainToken = Str::random(64);

        $invitation = Invitation::create([
            'tenant_id' => $tenantId,
            'invited_by' => $invitedByUserId,
            'email' => $email,
            'role' => $role,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(7),
        ]);

        return [$invitation, $plainToken];
    }
}
