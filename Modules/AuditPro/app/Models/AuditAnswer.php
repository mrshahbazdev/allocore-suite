<?php

namespace Modules\AuditPro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AuditPro\Models\Concerns\BelongsToCurrentTeam;

class AuditAnswer extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'auditpro_answers';

    protected $fillable = ['team_id', 'audit_id', 'question_id', 'value', 'comment', 'evidence_file_path'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AuditQuestion::class);
    }
}
