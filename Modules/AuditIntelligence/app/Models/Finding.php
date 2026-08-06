<?php

namespace Modules\AuditIntelligence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AuditIntelligence\Models\Concerns\BelongsToCurrentTeam;

class Finding extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'auditintelligence_findings';

    protected $fillable = [
        'audit_id',
        'title',
        'description',
        'risk_level',
        'priority',
        'legal_relevance',
        'implementation_effort',
        'status',
    ];

    protected $casts = [
        'audit_id' => 'integer',
    ];

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function upsells(): HasMany
    {
        return $this->hasMany(Upsell::class);
    }
}
