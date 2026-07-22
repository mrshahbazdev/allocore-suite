<?php

namespace Modules\FinancialPlatform\Services;

use App\Models\Team;
use Carbon\Carbon;
use Modules\FinancialPlatform\Models\BankTransaction;
use Modules\InvoiceMaker\Models\Invoice;

class CashflowForecastService
{
    public function forTeam(Team $team): array
    {
        $currentBalance = $this->currentBalance($team);
        $months = [];

        for ($i = 0; $i < 3; $i++) {
            $month = now()->addMonthsNoOverflow($i);
            $months[] = [
                'label' => $month->format('M Y'),
                'incoming' => $this->projectedIncoming($team, $month),
                'outgoing' => $this->projectedOutgoing($team, $month),
                'net' => $this->projectedIncoming($team, $month) - $this->projectedOutgoing($team, $month),
                'ending_balance' => null,
            ];
        }

        $running = $currentBalance;
        foreach ($months as $key => $month) {
            $running += $month['net'];
            $months[$key]['ending_balance'] = round($running, 2);
        }

        return [
            'current_balance' => round($currentBalance, 2),
            'months' => $months,
        ];
    }

    private function currentBalance(Team $team): float
    {
        $income = (float) BankTransaction::query()
            ->where('team_id', $team->id)
            ->where('type', 'income')
            ->sum('amount');

        $outgoing = (float) BankTransaction::query()
            ->where('team_id', $team->id)
            ->where('type', 'expense')
            ->sum('amount');

        return $income - $outgoing;
    }

    private function projectedIncoming(Team $team, Carbon $month): float
    {
        $openInvoices = (float) Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])
            ->whereYear('due_date', $month->year)
            ->whereMonth('due_date', $month->month)
            ->sum('amount_due');

        $sameMonthLastYear = $month->copy()->subMonthsNoOverflow(12);

        $recurringBank = (float) BankTransaction::query()
            ->where('team_id', $team->id)
            ->where('type', 'income')
            ->whereYear('transaction_date', $sameMonthLastYear->year)
            ->whereMonth('transaction_date', $sameMonthLastYear->month)
            ->avg('amount');

        return round($openInvoices + max(0, $recurringBank), 2);
    }

    private function projectedOutgoing(Team $team, Carbon $month): float
    {
        $expenseClass = 'Modules\\InvoiceMaker\\Models\\Expense';

        $sameMonthLastYear = $month->copy()->subMonthsNoOverflow(12);

        $budgeted = (float) BankTransaction::query()
            ->where('team_id', $team->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $sameMonthLastYear->year)
            ->whereMonth('transaction_date', $sameMonthLastYear->month)
            ->avg('amount');

        if (class_exists($expenseClass)) {
            $budgeted += (float) $expenseClass::withoutGlobalScopes()
                ->where('team_id', $team->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');
        }

        return round($budgeted, 2);
    }
}
