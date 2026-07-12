<?php

namespace Modules\AuditPro\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AuditPro\Models\Concerns\BelongsToCurrentTeam;

class AuditTemplate extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'auditpro_templates';

    protected $fillable = ['team_id', 'name', 'slug', 'description', 'created_by', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function pillars(): HasMany
    {
        return $this->hasMany(AuditPillar::class, 'template_id')->orderBy('position');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AuditQuestion::class, 'template_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
