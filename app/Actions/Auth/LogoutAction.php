<?php

namespace App\Actions\Auth;

use App\Models\User;

class LogoutAction
{
    public function execute(User $user): void
    {
        // Revoke only current token
        $user->currentAccessToken()?->delete();
    }
}
