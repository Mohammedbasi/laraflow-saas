<?php

namespace App\Actions\Reports;

use Barryvdh\DomPDF\Facade\Pdf;

class GenerateWeeklyReportPdfAction
{
    public function execute(string $tenantName, string $weekStart, string $weekEnd, array $stats): string
    {
        $pdf = Pdf::loadView('reports.weekly', [
            'tenantName' => $tenantName,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'stats' => $stats,
        ])->setPaper('a4');

        return $pdf->output(); // raw bytes
    }
}
