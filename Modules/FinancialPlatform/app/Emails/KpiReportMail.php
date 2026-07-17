<?php

namespace Modules\FinancialPlatform\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KpiReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $summary,
        public string $period,
        public string $teamName,
    ) {}

    public function build(): self
    {
        return $this->subject(__('KPI Report for :team', ['team' => $this->teamName]))
            ->markdown('financialplatform::emails.kpi-report');
    }
}
