<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivityAdminIndexRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;

class ActivityAdminController extends Controller
{
    public function index(ActivityAdminIndexRequest $request)
    {
        $data = $request->validated();

        $perPage = $data['per_page'] ?? 15;

        $q = Activity::withoutGlobalScopes()
            ->when($data['tenant_id'] ?? null, fn ($qb, $tenantId) => $qb->where('tenant_id', $tenantId))
            ->when($data['impersonated_by_user_id'] ?? null, fn ($qb, $id) => $qb->where('impersonated_by_user_id', $id))
            ->when($data['subject_type'] ?? null, fn ($qb, $type) => $qb->where('subject_type', $type))
            ->when($data['date_from'] ?? null, fn ($qb, $from) => $qb->whereDate('created_at', '>=', $from))
            ->when($data['date_to'] ?? null, fn ($qb, $to) => $qb->whereDate('created_at', '<=', $to))
            ->orderByDesc('id')
            ->paginate($perPage);

        return ActivityResource::collection($q);
    }
}
