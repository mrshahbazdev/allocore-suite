<?php

namespace Modules\AuditPro\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Support\Maturity;

#[Layout('layouts.shell')]
class AuditComparison extends Component
{
    public ?int $firstAuditId = null;

    public ?int $secondAuditId = null;

    public function mount(): void
    {
        $ids = $this->availableAudits()->pluck('id');
        $this->firstAuditId = $ids->get(0);
        $this->secondAuditId = $ids->get(1);
    }

    private function availableAudits()
    {
        return Audit::with(['template', 'creator', 'results'])
            ->where('status', 'completed')
            ->latest()
            ->get();
    }

    private function auditData(?int $id): ?array
    {
        if (! $id) {
            return null;
        }

        $audit = Audit::with(['template', 'creator', 'results'])->find($id);

        if (! $audit) {
            return null;
        }

        $score = (float) ($audit->results->avg('average_score') ?? 0);

        return [
            'audit' => $audit,
            'score' => $score,
            'maturity' => Maturity::label($score),
            'results' => $audit->results->keyBy('level'),
        ];
    }

    public function render()
    {
        return view('auditpro::livewire.audit-comparison', [
            'availableAudits' => $this->availableAudits(),
            'first' => $this->auditData($this->firstAuditId),
            'second' => $this->auditData($this->secondAuditId),
        ]);
    }
}
