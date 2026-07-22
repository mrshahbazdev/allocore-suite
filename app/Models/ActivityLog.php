<?php

namespace App\Models;

use App\Services\WebhookDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'team_id',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public static function log(string $logName, string $description, mixed $subject = null, mixed $causer = null, ?array $properties = null, ?int $teamId = null): self
    {
        $log = new self([
            'log_name' => $logName,
            'description' => $description,
            'properties' => $properties,
            'team_id' => $teamId,
        ]);

        if ($subject) {
            $log->subject()->associate($subject);
        }

        if ($causer) {
            $log->causer()->associate($causer);
        } else {
            $log->causer_id = auth()->id();
            $log->causer_type = auth()->check() ? User::class : null;
        }

        $log->save();

        WebhookDispatcher::dispatch('activity.created', [
            'log_name' => $log->log_name,
            'description' => $log->description,
            'subject_type' => $log->subject_type,
            'subject_id' => $log->subject_id,
            'causer_type' => $log->causer_type,
            'causer_id' => $log->causer_id,
            'team_id' => $log->team_id,
            'properties' => $log->properties,
            'created_at' => $log->created_at->toIso8601String(),
        ]);

        return $log;
    }
}
