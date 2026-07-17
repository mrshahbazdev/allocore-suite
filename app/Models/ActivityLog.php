<?php

namespace App\Models;

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

        return $log;
    }
}
