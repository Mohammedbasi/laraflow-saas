<?php

namespace App\Actions\Reports;

use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CalculateWeeklyTaskStatsAction
{
    public function execute(int $tenantId, Carbon $weekStart, Carbon $weekEnd): array
    {
        // Heavy query is okay here: because this action runs in a queued job.
        // Define "completed" as status=done and updated_at within week range
        $base = Task::query()
            ->where('tenant_id', $tenantId)
            ->where('status', Task::STATUS_DONE)
            ->whereBetween('updated_at', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()]);

        $completedCount = (clone $base)->count();

        $byProject = (clone $base)
            ->select('project_id', DB::raw('COUNT(*) as completed'))
            ->groupBy('project_id')
            ->orderByDesc('completed')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'project_id' => (int) $r->project_id,
                'completed' => (int) $r->completed,
            ])
            ->all();

        $byAssignee = (clone $base)
            ->select('assignee_id', DB::raw('COUNT(*) as completed'))
            ->groupBy('assignee_id')
            ->orderByDesc('completed')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'assignee_id' => $r->assignee_id ? (int) $r->assignee_id : null,
                'completed' => (int) $r->completed,
            ])
            ->all();

        return [
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'completed_tasks' => $completedCount,
            'top_projects' => $byProject,
            'top_assignees' => $byAssignee,
        ];
    }
}
