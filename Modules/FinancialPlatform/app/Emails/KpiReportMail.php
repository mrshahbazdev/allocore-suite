<?php

namespace Modules\FinancialPlatform\Emails;

use App\Mail\TemplatedMailable;
use Illuminate\Mail\Mailables\Content;

class KpiReportMail extends TemplatedMailable
{
    public function __construct(
        public array $summary,
        public string $period,
        public string $teamName,
    ) {}

    public function templateTool(): string
    {
        return 'financialplatform';
    }

    public function templateKey(): string
    {
        return 'kpi-report';
    }

    public function templateVariables(): array
    {
        return [
            'summaryRows' => $this->summary,
            'period' => $this->period,
            'teamName' => $this->teamName,
            'appName' => config('app.name'),
        ];
    }

    protected function defaultSubject(): string
    {
        return __('KPI Report for :team', ['team' => $this->teamName]);
    }

    protected function defaultContent(): Content
    {
        return new Content(
            view: 'financialplatform::emails.kpi-report',
            with: $this->templateVariables(),
        );
    }
}
