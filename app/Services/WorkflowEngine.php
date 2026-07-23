<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Workflow;
use App\Notifications\AlertNotification;
use App\Support\ModuleStats;
use Illuminate\Support\Facades\Http;
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
            'send_webhook' => self::sendWebhook($workflow, $log),
            'create_record' => self::createRecord($workflow, $log),
            default => null,
        };
    }

    protected static function sendNotification(Workflow $workflow, ActivityLog $log): void
    {
        $payload = $workflow->action_payload ?? [];
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

    protected static function sendWebhook(Workflow $workflow, ActivityLog $log): void
    {
        $payload = $workflow->action_payload ?? [];
        $url = $payload['url'] ?? null;

        if (! $url) {
            return;
        }

        Http::post($url, [
            'workflow' => $workflow->name,
            'event' => $log->log_name,
            'subject_type' => $log->subject_type,
            'subject_id' => $log->subject_id,
            'team_id' => $log->team_id,
            'description' => $log->description,
            'properties' => $log->properties,
        ]);
    }

    protected static function createRecord(Workflow $workflow, ActivityLog $log): void
    {
        $payload = $workflow->action_payload ?? [];
        $moduleKey = $payload['module'] ?? null;
        $name = $payload['name'] ?? ($workflow->name.' record');

        if (! $moduleKey) {
            return;
        }

        $modelClass = app(ModuleStats::class)->modelFor($moduleKey);

        if (! $modelClass || ! class_exists($modelClass)) {
            return;
        }

        $model = new $modelClass;
        $fillable = method_exists($model, 'getFillable') ? $model->getFillable() : [];
        $data = ['name' => $name];

        if (in_array('team_id', $fillable, true)) {
            $data['team_id'] = $workflow->team_id;
        }

        if (in_array('user_id', $fillable, true)) {
            $data['user_id'] = $workflow->user_id;
        }

        if (in_array('title', $fillable, true) && ! in_array('name', $fillable, true)) {
            $data['title'] = $data['name'];
            unset($data['name']);
        }

        if (in_array('status', $fillable, true)) {
            $data['status'] = 'open';
        }

        $modelClass::create($data);
    }
}
