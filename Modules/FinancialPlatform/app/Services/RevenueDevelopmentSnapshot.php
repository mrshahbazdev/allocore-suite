<?php

namespace Modules\FinancialPlatform\Services;

use App\Models\Team;
use Modules\FinancialPlatform\Models\Analysis;
use Modules\FinancialPlatform\Models\Setting;
use Modules\InvoiceMaker\Models\Invoice;

class RevenueDevelopmentSnapshot
{
    public function forTeam(?Team $team): array
    {
        if (! $team) {
            return $this->emptyState();
        }

        $target = (float) (Setting::query()
            ->where('team_id', $team->id)
            ->where('key', 'revenue_development_target_sales')
            ->value('value') ?? 0);

        $source = (string) (Setting::query()
            ->where('team_id', $team->id)
            ->where('key', 'revenue_development_actual_source')
            ->value('value') ?? 'invoicemaker');

        $manualActual = Setting::query()
            ->where('team_id', $team->id)
            ->where('key', 'revenue_development_actual_manual')
            ->value('value');

        $actual = $this->resolveActualSales($team, $source, $manualActual);
        $percentage = $target > 0 ? round(($actual / $target) * 100, 1) : 0.0;

        return [
            'targetSales' => $target,
            'actualSales' => $actual,
            'percentage' => $percentage,
            'source' => $source,
            'sourceLabel' => $this->sourceLabel($source),
            'status' => $this->status($percentage),
            'availableSources' => [
                'analysis' => 'Financial analyses',
                'invoicemaker' => 'InvoiceMaker',
                'seostory' => 'SeoStory financial analyses',
                'manual' => 'Manual entry',
            ],
        ];
    }

    private function emptyState(): array
    {
        return [
            'targetSales' => 0.0,
            'actualSales' => 0.0,
            'percentage' => 0.0,
            'source' => 'invoicemaker',
            'sourceLabel' => 'InvoiceMaker',
            'status' => 'neutral',
            'availableSources' => [
                'analysis' => 'Financial analyses',
                'invoicemaker' => 'InvoiceMaker',
                'seostory' => 'SeoStory financial analyses',
                'manual' => 'Manual entry',
            ],
        ];
    }

    private function resolveActualSales(Team $team, string $source, mixed $manualActual): float
    {
        return match ($source) {
            'manual' => (float) ($manualActual ?? 0),
            'analysis' => $this->analysisRevenue($team),
            'seostory' => $this->seostoryRevenue($team),
            default => $this->invoiceMakerRevenue($team),
        };
    }

    private function invoiceMakerRevenue(Team $team): float
    {
        return (float) Invoice::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereYear('invoice_date', now()->year)
            ->whereMonth('invoice_date', now()->month)
            ->sum('amount_paid');
    }

    private function analysisRevenue(Team $team): float
    {
        return (float) Analysis::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->with(['gmbhInput', 'jahresabschlussInputs' => fn ($query) => $query->latest('year_order')->limit(1)])
            ->get()
            ->sum(function (Analysis $analysis): float {
                if ($analysis->gmbhInput?->revenue_current !== null) {
                    return (float) $analysis->gmbhInput->revenue_current;
                }

                $latestRevenue = $analysis->jahresabschlussInputs->first()?->revenue;

                return (float) ($latestRevenue ?? 0);
            });
    }

    private function seostoryRevenue(Team $team): float
    {
        $api = app(SeoStoryApiClient::class);
        $fetched = $api->revenue(now()->year, now()->month);

        if ($fetched !== null) {
            Setting::updateOrCreate(
                ['team_id' => $team->id, 'key' => 'revenue_development_seostory_revenue'],
                ['value' => (string) $fetched, 'type' => 'string']
            );

            return $fetched;
        }

        return (float) Setting::query()
            ->where('team_id', $team->id)
            ->where('key', 'revenue_development_seostory_revenue')
            ->value('value');
    }

    private function sourceLabel(string $source): string
    {
        return match ($source) {
            'analysis' => 'Financial analyses',
            'seostory' => 'SeoStory financial analyses',
            'manual' => 'Manual entry',
            default => 'InvoiceMaker',
        };
    }

    private function status(float $percentage): string
    {
        return match (true) {
            $percentage >= 100 => 'green',
            $percentage >= 80 => 'yellow',
            default => 'red',
        };
    }
}
