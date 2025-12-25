<?php

namespace App\Jobs\Reports;

use App\Actions\Reports\GenerateWeeklyReportPdfAction;
use App\Models\Tenant;
use App\Models\WeeklyReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateWeeklyReportPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $reportId,
        public int $tenantId,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GenerateWeeklyReportPdfAction $action): void
    {
        $report = WeeklyReport::query()->findOrFail($this->reportId);
        $tenant = Tenant::query()->findOrFail($this->tenantId);

        $bytes = $action->execute(
            tenantName: $tenant->name,
            weekStart: $report->week_start->toDateString(),
            weekEnd: $report->week_end->toDateString(),
            stats: $report->stats ?? [],
        );

        $disk = $disk = config('reports.disk');
        $path = sprintf(
            'tenant_%d/weekly_%s_%s.pdf',
            $tenant->id,
            $report->week_start->toDateString(),
            $report->week_end->toDateString(),
        );

        Storage::disk($disk)->put($path, $bytes);

        $report->update([
            'pdf_disk' => $disk,
            'pdf_path' => $path,
            'status' => 'pdf_ready',
        ]);
    }

    public function failed(\Throwable $e): void
    {
        WeeklyReport::query()
            ->whereKey($this->reportId)
            ->update(['status' => 'failed', 'last_error' => $e->getMessage()]);
    }
}
