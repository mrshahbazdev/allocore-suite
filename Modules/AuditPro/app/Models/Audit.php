<?php

namespace Modules\AuditPro\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AuditPro\Models\Concerns\BelongsToCurrentTeam;

class Audit extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'auditpro_audits';

    protected $fillable = [
        'team_id', 'template_id', 'created_by', 'status',
        'company_name', 'industry', 'size', 'company_age',
        'audit_type', 'focus_pillar', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'company_age' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AuditTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AuditAnswer::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(AuditResult::class);
    }
}
