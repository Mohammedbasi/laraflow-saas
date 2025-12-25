<?php

namespace App\Jobs\Reports;

use App\Actions\Reports\CalculateWeeklyTaskStatsAction;
use App\Models\WeeklyReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CalculateWeeklyTaskStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $reportId,
        public int $tenantId,
        public string $weekStart,
        public string $weekEnd,
    ) {
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     */
    public function handle(CalculateWeeklyTaskStatsAction $action): void
    {
        $report = WeeklyReport::query()->findOrFail($this->reportId);

        $stats = $action->execute(
            tenantId: $this->tenantId,
            weekStart: Carbon::parse($this->weekStart),
            weekEnd: Carbon::parse($this->weekEnd),
        );

        $report->update([
            'stats' => $stats,
            'status' => 'stats_ready',
        ]);
    }

    public function failed(\Throwable $e): void
    {
        WeeklyReport::query()
            ->whereKey($this->reportId)
            ->update(['status' => 'failed', 'last_error' => $e->getMessage()]);
    }
}
