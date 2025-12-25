<?php

namespace App\Jobs\Reports;

use App\Mail\WeeklyReportMail;
use App\Models\User;
use App\Models\WeeklyReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class EmailWeeklyReportJob implements ShouldQueue
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
    public function handle(Mailer $mailer): void
    {
        $report = WeeklyReport::query()->findOrFail($this->reportId);

        $recipients = User::query()
            ->where('tenant_id', $this->tenantId)
            ->where('is_manager', true)
            ->get();

        if ($recipients->isEmpty()) {
            // fallback
            $report->update(['status' => 'failed', 'last_error' => 'No recipients found.']);

            return;
        }

        $disk = $report->pdf_disk;
        $path = $report->pdf_path;

        $attachmentBytes = Storage::disk($disk)->get($path);
        $filename = basename($path);

        foreach ($recipients as $user) {
            $mailable = (new WeeklyReportMail($report))
                ->attachData($attachmentBytes, $filename, ['mime' => 'application/pdf']);

            $mailer->to($user->email)->send($mailable);
        }

        $report->update(['status' => 'emailed']);
    }

    public function failed(\Throwable $e): void
    {
        WeeklyReport::query()
            ->whereKey($this->reportId)
            ->update(['status' => 'failed', 'last_error' => $e->getMessage()]);
    }
}
