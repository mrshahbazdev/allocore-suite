<?php

namespace Modules\AuditIntelligence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AuditIntelligence\Models\Concerns\BelongsToCurrentTeam;

class Upsell extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'auditintelligence_upsells';

    protected $fillable = [
        'finding_id',
        'type',
        'name',
        'description',
        'link',
    ];

    protected $casts = [
        'finding_id' => 'integer',
    ];

    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class);
    }
}
