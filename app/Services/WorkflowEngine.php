<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Workflow;
use App\Notifications\AlertNotification;
use Illuminate\Support\Str;

class WorkflowEngine
{
    public static function evaluate(ActivityLog $log): void
    {
        $workflows = Workflow::where('is_active', true)
            ->where('trigger_event', $log->log_name)
            ->get();

        foreach ($workflows as $workflow) {
            if (! self::matchesSubject($log, $workflow)) {
                continue;
            }

            self::run($workflow, $log);
        }
    }

    protected static function matchesSubject(ActivityLog $log, Workflow $workflow): bool
    {
        if (blank($workflow->subject_type)) {
            return true;
        }

        return $log->subject_type !== null && Str::contains($log->subject_type, $workflow->subject_type);
    }

    protected static function run(Workflow $workflow, ActivityLog $log): void
    {
        match ($workflow->action) {
            'send_notification' => self::sendNotification($workflow, $log),
            default => null,
        };
    }

    protected static function sendNotification(Workflow $workflow, ActivityLog $log): void
    {
        $payload = $workflow->action_payload ?? [];
        $message = $payload['message'] ?? __('Workflow triggered');
        $user = $workflow->user;

        if (! $user) {
            return;
        }

        $user->notify(new AlertNotification(
            new Alert([
                'name' => $workflow->name,
                'metric' => 'custom',
                'operator' => '>',
                'threshold' => 0,
            ]),
            0
        ));
    }
}
