<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Notifications\AlertNotification;
use App\Support\AlertEvaluator;
use Illuminate\Console\Command;

class CheckAlerts extends Command
{
    protected $signature = 'app:check-alerts';

    protected $description = 'Evaluate active user alerts and send notifications';

    public function handle(AlertEvaluator $evaluator): int
    {
        $alerts = Alert::with('user')
            ->where('is_active', true)
            ->get();

        foreach ($alerts as $alert) {
            if (! $alert->user || ! $alert->team_id) {
                continue;
            }

            $value = $evaluator->evaluate($alert, $alert->team_id);

            if ($value === null) {
                continue;
            }

            $alert->last_value = $value;

            if ($evaluator->triggered($alert, $value)) {
                $shouldNotify = $alert->last_triggered_at === null || $alert->last_triggered_at->diffInHours(now()) >= 1;

                if ($shouldNotify) {
                    $alert->user->notify(new AlertNotification($alert, $value));
                    $alert->last_triggered_at = now();
                }
            }

            $alert->save();
        }

        $this->info("Checked {$alerts->count()} alerts.");

        return self::SUCCESS;
    }
}
