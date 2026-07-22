<?php

namespace App\Support;

use App\Models\Alert;
use Modules\CashCore\Models\CashTransaction;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\KpiTool\Models\KpiValue;
use Modules\TimeButler\Models\AbsenceRequest;

class AlertEvaluator
{
    public function evaluate(Alert $alert, int $teamId): ?float
    {
        return match ($alert->metric) {
            'overdue_invoices' => $this->overdueInvoices($teamId),
            'low_cash' => $this->cashBalance($teamId),
            'kpi_critical' => $this->kpiCritical($teamId),
            'pending_absences' => $this->pendingAbsences($teamId),
            default => null,
        };
    }

    public function triggered(Alert $alert, float $value): bool
    {
        return match ($alert->operator) {
            '>' => $value > $alert->threshold,
            '<' => $value < $alert->threshold,
            '>=' => $value >= $alert->threshold,
            '<=' => $value <= $alert->threshold,
            '=' => (float) $value === (float) $alert->threshold,
            default => false,
        };
    }

    protected function overdueInvoices(int $teamId): float
    {
        if (! class_exists(Invoice::class)) {
            return 0;
        }

        return Invoice::where('team_id', $teamId)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->count();
    }

    protected function cashBalance(int $teamId): float
    {
        if (! class_exists(CashTransaction::class)) {
            return 0;
        }

        $income = CashTransaction::where('team_id', $teamId)->where('type', 'income')->sum('amount');
        $expense = CashTransaction::where('team_id', $teamId)->where('type', 'expense')->sum('amount');

        return (float) $income - (float) $expense;
    }

    protected function kpiCritical(int $teamId): float
    {
        if (! class_exists(KpiValue::class)) {
            return 0;
        }

        return KpiValue::whereHas('kpiDefinition', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })->where('status', 'critical')->count();
    }

    protected function pendingAbsences(int $teamId): float
    {
        if (! class_exists(AbsenceRequest::class)) {
            return 0;
        }

        return AbsenceRequest::where('team_id', $teamId)->where('status', 'pending')->count();
    }
}
