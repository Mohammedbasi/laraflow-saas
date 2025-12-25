<?php

namespace App\Console\Commands;

use App\Jobs\Reports\CalculateWeeklyTaskStatsJob;
use App\Jobs\Reports\EmailWeeklyReportJob;
use App\Jobs\Reports\GenerateWeeklyReportPdfJob;
use App\Models\Tenant;
use App\Models\WeeklyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;

class GenerateWeeklyReportsCommand extends Command
{
    protected $signature = 'reports:weekly {--tenant_id=} {--week_start=} {--week_end=}';

    protected $description = 'Generate and email weekly completed tasks reports for tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant_id');

        $weekStart = $this->option('week_start')
            ? Carbon::parse($this->option('week_start'))->startOfDay()
            : now()->subWeek()->startOfWeek(); // adjust to your locale (Mon/Sun)

        $weekEnd = $this->option('week_end')
            ? Carbon::parse($this->option('week_end'))->endOfDay()
            : now()->subWeek()->endOfWeek();

        $tenants = Tenant::query()
            ->when($tenantId, fn ($q) => $q->whereKey($tenantId))
            ->get();

        foreach ($tenants as $tenant) {
            // Idempotency: do not create duplicates for same period
            $report = WeeklyReport::query()->firstOrCreate([
                'tenant_id' => $tenant->id,
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
            ], [
                'status' => 'pending',
            ]);

            // If already emailed, skip (you can add --force later)
            if ($report->status === 'emailed') {
                continue;
            }

            Bus::chain([
                new CalculateWeeklyTaskStatsJob(
                    $report->id,
                    (int) $tenant->id,
                    $weekStart->toDateString(),
                    $weekEnd->toDateString()
                ),
                new GenerateWeeklyReportPdfJob(
                    $report->id,
                    (int) $tenant->id
                ),
                new EmailWeeklyReportJob(
                    $report->id,
                    (int) $tenant->id
                ),
            ])->dispatch();
        }

        $this->info('Weekly report jobs dispatched.');

        return self::SUCCESS;
    }
}
