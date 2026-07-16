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
            'profit' => $this->profit(),
            'order' => $this->order(),
            'influence' => $this->influence(),
            'legacy' => $this->legacy(),
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

    private function profit(): array
    {
        return [
            'note' => 'Concrete profit KPIs are not defined in the Deep KPI sheet. Use the AuditPro maturity assessment for these criteria.',
        ];
    }

    private function order(): array
    {
        return [
            'note' => 'Concrete order KPIs are not defined in the Deep KPI sheet. Use the AuditPro maturity assessment for these criteria.',
        ];
    }

    private function influence(): array
    {
        return [
            'note' => 'Concrete influence KPIs are not defined in the Deep KPI sheet. Use the AuditPro maturity assessment for these criteria.',
        ];
    }

    private function legacy(): array
    {
        return [
            'note' => 'Concrete legacy KPIs are not defined in the Deep KPI sheet. Use the AuditPro maturity assessment for these criteria.',
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
}
