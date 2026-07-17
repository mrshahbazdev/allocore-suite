<?php

namespace Modules\AuditPro\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Support\Maturity;

class AuditPdfService
{
    public function output(Audit $audit): string
    {
        $audit->load(['team', 'template.pillars', 'results', 'creator']);
        $overallScore = (float) ($audit->results->avg('average_score') ?? 0);
        $overallMaturity = Maturity::label($overallScore);

        return Pdf::loadView('auditpro::report-pdf', compact('audit', 'overallScore', 'overallMaturity'))->output();
    }

    public function download(Audit $audit)
    {
        return response()->streamDownload(
            fn () => print $this->output($audit),
            __('Audit-Report').'-'.$audit->id.'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
