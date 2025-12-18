<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityIndexRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(ActivityIndexRequest $request)
    {
        $this->authorize('viewAny', Activity::class);

        $data = $request->validated();
        $perPage = $data['per_page'] ?? 15;

        // Uses TenantScope automatically (tenant_id filtered)
        $q = Activity::query()
            ->when($data['impersonated_by_user_id'] ?? null, fn ($qb, $id) => $qb->where('impersonated_by_user_id', $id))
            ->when($data['subject_type'] ?? null, fn ($qb, $type) => $qb->where('subject_type', $type))
            ->when($data['date_from'] ?? null, fn ($qb, $from) => $qb->whereDate('created_at', '>=', $from))
            ->when($data['date_to'] ?? null, fn ($qb, $to) => $qb->whereDate('created_at', '<=', $to))
            ->orderByDesc('id')
            ->paginate($perPage);

        return ActivityResource::collection($q);
    }
}
