<?php

namespace App\Support\Audit;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(
        ?int $tenantId,
        ?int $causerId,
        ?int $impersonatedByUserId,
        ?Model $subject,
        string $description,
        array $meta = []
    ): Activity {
        return Activity::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'causer_id' => $causerId,
            'impersonated_by_user_id' => $impersonatedByUserId,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'meta' => $meta ?: null,
        ]);
    }

    public function actorContext(Request $request): array
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        return [
            'causer_id' => $user?->id,
            'impersonated_by_user_id' => $token?->impersonated_by_user_id,
        ];
    }
}
