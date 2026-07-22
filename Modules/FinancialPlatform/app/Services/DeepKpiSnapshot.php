<?php

namespace Modules\FinancialPlatform\Services;

use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\FinancialPlatform\Models\Setting;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\LeadQuality\Models\Contact;

class DeepKpiSnapshot
{
    public function __construct(
        private readonly RevenueDevelopmentSnapshot $revenueDevelopment,
    ) {}

    public function forTeam(?Team $team): array
    {
        if (! $team) {
            return $this->emptyState();
        }

        $revenueDevelopment = $this->revenueDevelopment->forTeam($team);

        return [
            'revenue' => $this->revenue($team, $revenueDevelopment),
            'profit' => $this->profit($team),
            'order' => $this->order($team),
            'influence' => $this->influence($team),
            'legacy' => $this->legacy($team),
        ];
    }

    private function emptyState(): array
    {
        return [
            'revenue' => [
                'umsatzbedarf' => [
                    'target' => 0.0,
                    'actual' => 0.0,
                    'ratio' => null,
                    'achievement' => 0.0,
                    'status' => 'neutral',
                    'source' => 'invoicemaker',
                ],
                'leadQuality' => [],
                'abschlussquote' => [],
                'vertragstreue' => [],
            ],
            'profit' => [],
            'order' => [],
            'influence' => [],
            'legacy' => [],
        ];
    }

    private function revenue(Team $team, array $revenueDevelopment): array
    {
        $target = (float) ($revenueDevelopment['targetSales'] ?? 0);
        $actual = (float) ($revenueDevelopment['actualSales'] ?? 0);

        return [
            'umsatzbedarf' => [
                'target' => $target,
                'actual' => $actual,
                'ratio' => $actual > 0 ? round($target / $actual, 4) : null,
                'achievement' => $revenueDevelopment['percentage'] ?? 0.0,
                'status' => $revenueDevelopment['status'] ?? 'neutral',
                'source' => $revenueDevelopment['source'] ?? 'invoicemaker',
                'sourceLabel' => $revenueDevelopment['sourceLabel'] ?? 'InvoiceMaker',
            ],
            'leadQuality' => $this->leadQuality($team),
            'abschlussquote' => $this->abschlussquote($team),
            'vertragstreue' => $this->vertragstreue($team),
        ];
    }

    private function profit(Team $team): array
    {
        $now = now();
        $revenue = $this->monthlyRevenue($team, $now);
        $expenses = $this->monthlyExpenses($team, $now);
        $margin = $revenue > 0 ? round((($revenue - $expenses) / $revenue) * 100, 1) : null;

        return [
            'maturity' => $this->maturityForPillar($team, ['Gewinn', 'Profit']),
            'revenue' => $revenue,
            'expenses' => $expenses,
            'margin_percent' => $margin,
            'repeat_customer_rate' => $this->repeatCustomerRate($team, $now),
            'cash_reserves_months' => $this->cashReserveMonths($team, $now),
            'debt_ratio' => $this->debtRatio($team),
        ];
    }

    private function order(Team $team): array
    {
        $now = now();

        return [
            'maturity' => $this->maturityForPillar($team, ['Ordnung', 'Order']),
            'on_time_payment_rate' => $this->onTimePaymentRate($team, $now),
            'paid_invoice_ratio' => $this->paidInvoiceRatio($team, $now),
            'average_payment_days' => $this->averagePaymentDaysInMonth($team, $now)['days'],
            'process_efficiency_score' => $this->processEfficiencyScore($team, $now),
        ];
    }

    private function influence(Team $team): array
    {
        return [
            'maturity' => $this->maturityForPillar($team, ['Einfluss', 'Influence']),
            'conversion_rate' => ($this->abschlussquote($team)['conversionRateCurrent'] ?? null),
            'average_invoice_value' => $this->averageInvoiceValue($team),
            'customer_count' => $this->customerCount($team),
        ];
    }

    private function legacy(Team $team): array
    {
        return [
            'maturity' => $this->maturityForPillar($team, ['Vermächtnis', 'Legacy']),
            'repeat_customer_rate' => $this->repeatCustomerRate($team, now()),
            'average_customer_lifetime_value' => $this->averageCustomerLifetimeValue($team),
            'retention_score' => $this->retentionScore($team),
        ];
    }

    private function leadQuality(Team $team): array
    {
        $metrics = [
            'impressions' => 'Webseiten Impressionen',
            'clicks' => 'Klicks',
            'ctr' => 'CTR',
            'average_position' => 'Durchschnittliche Google Position',
            'page_value' => 'Seiten Wert',
        ];

        $current = [];
        foreach ($metrics as $key => $label) {
            $previousKey = "deep_kpi_{$key}_previous";
            $currentKey = "deep_kpi_{$key}_current";

            $previousValue = (float) Setting::get($previousKey, 0);
            $currentValue = (float) Setting::get($currentKey, 0);

            if ($key === 'ctr' && $currentValue === 0.0) {
                $impressions = (float) Setting::get('deep_kpi_impressions_current', 0);
                $clicks = (float) Setting::get('deep_kpi_clicks_current', 0);
                if ($impressions > 0) {
                    $currentValue = round(($clicks / $impressions) * 100, 2);
                }
            }

            $ratio = $previousValue > 0 ? round($currentValue / $previousValue, 4) : null;
            $changePercent = $previousValue > 0 ? round((($currentValue - $previousValue) / $previousValue) * 100, 1) : null;

            $current[$key] = [
                'label' => $label,
                'current' => $currentValue,
                'previous' => $previousValue,
                'ratio' => $ratio,
                'changePercent' => $changePercent,
            ];
        }

        return $current;
    }

    private function abschlussquote(Team $team): array
    {
        $now = Carbon::now();
        $previous = Carbon::now()->subMonth();

        $leadsCurrent = $this->leadCount($team, $now);
        $leadsPrevious = $this->leadCount($team, $previous);

        $customersCurrent = $this->newCustomerCount($team, $now);
        $customersPrevious = $this->newCustomerCount($team, $previous);

        $rateCurrent = $leadsCurrent > 0 ? round(($customersCurrent / $leadsCurrent) * 100, 1) : null;
        $ratePrevious = $leadsPrevious > 0 ? round(($customersPrevious / $leadsPrevious) * 100, 1) : null;

        return [
            'leadsCurrent' => $leadsCurrent,
            'leadsPrevious' => $leadsPrevious,
            'newCustomersCurrent' => $customersCurrent,
            'newCustomersPrevious' => $customersPrevious,
            'conversionRateCurrent' => $rateCurrent,
            'conversionRatePrevious' => $ratePrevious,
            'changePercent' => $ratePrevious > 0 ? round((($rateCurrent - $ratePrevious) / $ratePrevious) * 100, 1) : null,
        ];
    }

    private function leadCount(Team $team, Carbon $month): int
    {
        return Contact::query()
            ->where('team_id', $team->id)
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();
    }

    private function newCustomerCount(Team $team, Carbon $month): int
    {
        return Client::query()
            ->where('team_id', $team->id)
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();
    }

    private function vertragstreue(Team $team): array
    {
        $now = Carbon::now();
        $previous = Carbon::now()->subMonth();

        $current = $this->averagePaymentDays($team, $now);
        $prev = $this->averagePaymentDays($team, $previous);

        return [
            'averageDaysCurrent' => $current['days'],
            'invoicesCurrent' => $current['count'],
            'averageDaysPrevious' => $prev['days'],
            'invoicesPrevious' => $prev['count'],
            'changeDays' => $prev['days'] !== null && $current['days'] !== null ? round($current['days'] - $prev['days'], 1) : null,
        ];
    }

    private function averagePaymentDays(Team $team, Carbon $month): array
    {
        $driver = DB::connection()->getDriverName();

        $diffExpression = match ($driver) {
            'sqlite' => 'AVG(JULIANDAY(p.date) - JULIANDAY(i.invoice_date))',
            default => 'AVG(DATEDIFF(p.date, i.invoice_date))',
        };

        $result = DB::table('invoicemaker_payments as p')
            ->join('invoicemaker_invoices as i', 'i.id', '=', 'p.invoice_id')
            ->where('p.team_id', $team->id)
            ->whereYear('p.date', $month->year)
            ->whereMonth('p.date', $month->month)
            ->where('i.type', Invoice::TYPE_INVOICE)
            ->selectRaw("{$diffExpression} as avg_days, COUNT(DISTINCT i.id) as invoice_count")
            ->first();

        return [
            'days' => $result?->avg_days !== null ? (float) $result->avg_days : null,
            'count' => (int) ($result?->invoice_count ?? 0),
        ];
    }

    private function maturityForPillar(Team $team, array $names): ?array
    {
        $auditClass = 'Modules\\AuditPro\\Models\\Audit';
        if (! class_exists($auditClass)) {
            return null;
        }

        $audit = $auditClass::query()
            ->where('team_id', $team->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (! $audit) {
            return null;
        }

        $result = $audit->results()
            ->whereIn('level', $names)
            ->orWhereHas('pillar', function ($query) use ($names): void {
                $query->whereIn('name', $names);
            })
            ->first();

        if (! $result) {
            return null;
        }

        $score = (float) $result->average_score;

        return [
            'score' => $score,
            'label' => $this->maturityLabel($score),
            'pillar' => $result->level,
        ];
    }

    private function maturityLabel(float $score): string
    {
        return match (true) {
            $score >= 4.5 => 'Excellent',
            $score >= 3.5 => 'Strong',
            $score >= 2.5 => 'Solid',
            $score >= 1.5 => 'Weak',
            default => 'Critical',
        };
    }

    private function monthlyRevenue(Team $team, Carbon $month): float
    {
        return (float) Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereYear('invoice_date', $month->year)
            ->whereMonth('invoice_date', $month->month)
            ->sum('amount_paid');
    }

    private function monthlyExpenses(Team $team, Carbon $month): float
    {
        $expenseClass = 'Modules\\InvoiceMaker\\Models\\Expense';
        if (! class_exists($expenseClass)) {
            return 0.0;
        }

        return (float) $expenseClass::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->sum('amount');
    }

    private function repeatCustomerRate(Team $team, Carbon $month): ?float
    {
        $clients = Client::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->whereHas('invoices', function ($query) use ($month): void {
                $query->where('type', Invoice::TYPE_INVOICE)
                    ->whereYear('invoice_date', $month->year)
                    ->whereMonth('invoice_date', $month->month);
            })
            ->withCount(['invoices' => function ($query) use ($month): void {
                $query->where('type', Invoice::TYPE_INVOICE)
                    ->whereYear('invoice_date', $month->year)
                    ->whereMonth('invoice_date', $month->month);
            }])
            ->get();

        $total = $clients->count();
        if ($total === 0) {
            return null;
        }

        $repeat = $clients->where('invoices_count', '>', 1)->count();

        return round(($repeat / $total) * 100, 1);
    }

    private function cashReserveMonths(Team $team, Carbon $month): ?float
    {
        $expenseClass = 'Modules\\InvoiceMaker\\Models\\Expense';
        $monthlyExpenses = $this->monthlyExpenses($team, $month);

        if ($monthlyExpenses <= 0) {
            return null;
        }

        $end = $month->copy()->endOfMonth();

        $cash = (float) Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->where('status', Invoice::STATUS_PAID)
            ->whereDate('invoice_date', '<=', $end)
            ->sum('amount_paid');

        if (class_exists($expenseClass)) {
            $cash -= (float) $expenseClass::withoutGlobalScopes()
                ->where('team_id', $team->id)
                ->whereDate('date', '<=', $end)
                ->sum('amount');
        }

        return round(max(0, $cash) / $monthlyExpenses, 1);
    }

    private function debtRatio(Team $team): ?float
    {
        $totalDue = (float) Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])
            ->sum('amount_due');

        if ($totalDue <= 0) {
            return null;
        }

        $overdue = (float) Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->where('status', Invoice::STATUS_OVERDUE)
            ->sum('amount_due');

        return round(($overdue / $totalDue) * 100, 1);
    }

    private function onTimePaymentRate(Team $team, Carbon $month): ?float
    {
        $driver = DB::connection()->getDriverName();
        $diff = $driver === 'sqlite' ? 'JULIANDAY(p.date) - JULIANDAY(i.due_date)' : 'DATEDIFF(p.date, i.due_date)';

        $paidCount = (int) DB::table('invoicemaker_payments as p')
            ->join('invoicemaker_invoices as i', 'i.id', '=', 'p.invoice_id')
            ->where('p.team_id', $team->id)
            ->where('i.type', Invoice::TYPE_INVOICE)
            ->whereYear('p.date', $month->year)
            ->whereMonth('p.date', $month->month)
            ->where('i.status', Invoice::STATUS_PAID)
            ->count();

        if ($paidCount === 0) {
            return null;
        }

        $onTimeCount = (int) DB::table('invoicemaker_payments as p')
            ->join('invoicemaker_invoices as i', 'i.id', '=', 'p.invoice_id')
            ->where('p.team_id', $team->id)
            ->where('i.type', Invoice::TYPE_INVOICE)
            ->whereYear('p.date', $month->year)
            ->whereMonth('p.date', $month->month)
            ->where('i.status', Invoice::STATUS_PAID)
            ->whereRaw("{$diff} <= 0")
            ->count();

        return round(($onTimeCount / $paidCount) * 100, 1);
    }

    private function paidInvoiceRatio(Team $team, Carbon $month): ?float
    {
        $invoices = Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereYear('invoice_date', $month->year)
            ->whereMonth('invoice_date', $month->month)
            ->count();

        if ($invoices === 0) {
            return null;
        }

        $paid = Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereYear('invoice_date', $month->year)
            ->whereMonth('invoice_date', $month->month)
            ->where('status', Invoice::STATUS_PAID)
            ->count();

        return round(($paid / $invoices) * 100, 1);
    }

    private function averagePaymentDaysInMonth(Team $team, Carbon $month): array
    {
        $driver = DB::connection()->getDriverName();
        $diffExpression = $driver === 'sqlite' ? 'AVG(JULIANDAY(p.date) - JULIANDAY(i.invoice_date))' : 'AVG(DATEDIFF(p.date, i.invoice_date))';

        $result = DB::table('invoicemaker_payments as p')
            ->join('invoicemaker_invoices as i', 'i.id', '=', 'p.invoice_id')
            ->where('p.team_id', $team->id)
            ->whereYear('p.date', $month->year)
            ->whereMonth('p.date', $month->month)
            ->where('i.type', Invoice::TYPE_INVOICE)
            ->selectRaw("{$diffExpression} as avg_days, COUNT(DISTINCT i.id) as invoice_count")
            ->first();

        return [
            'days' => $result?->avg_days !== null ? (float) $result->avg_days : null,
            'count' => (int) ($result?->invoice_count ?? 0),
        ];
    }

    private function processEfficiencyScore(Team $team, Carbon $month): ?float
    {
        $paidRatio = $this->paidInvoiceRatio($team, $month);
        $onTime = $this->onTimePaymentRate($team, $month);

        if ($paidRatio === null || $onTime === null) {
            return null;
        }

        return round(($paidRatio + $onTime) / 2, 1);
    }

    private function averageInvoiceValue(Team $team): ?float
    {
        $avg = Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->avg('amount_paid');

        return $avg !== null ? round((float) $avg, 2) : null;
    }

    private function customerCount(Team $team): int
    {
        return Client::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->count();
    }

    private function averageCustomerLifetimeValue(Team $team): ?float
    {
        $clients = Client::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with('invoices')
            ->get();

        if ($clients->isEmpty()) {
            return null;
        }

        return round($clients->avg(fn (Client $client): float => (float) $client->invoices->sum('amount_paid')), 2);
    }

    private function retentionScore(Team $team): ?float
    {
        $previous = now()->subMonth();
        $current = now();

        $previousClients = Client::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->whereHas('invoices', function ($query) use ($previous): void {
                $query->where('type', Invoice::TYPE_INVOICE)
                    ->whereYear('invoice_date', $previous->year)
                    ->whereMonth('invoice_date', $previous->month);
            })
            ->pluck('id');

        if ($previousClients->isEmpty()) {
            return null;
        }

        $retained = Client::withoutGlobalScopes()
            ->whereIn('id', $previousClients)
            ->whereHas('invoices', function ($query) use ($current): void {
                $query->where('type', Invoice::TYPE_INVOICE)
                    ->whereYear('invoice_date', $current->year)
                    ->whereMonth('invoice_date', $current->month);
            })
            ->count();

        return round(($retained / $previousClients->count()) * 100, 1);
    }
}
