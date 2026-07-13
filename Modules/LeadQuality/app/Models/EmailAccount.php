<?php

namespace Modules\LeadQuality\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\LeadQuality\Models\Concerns\BelongsToCurrentTeam;

class EmailAccount extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'leadquality_email_accounts';

    protected $fillable = [
        'team_id',
        'user_id',
        'email_address',
        'provider',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'username',
        'password',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'is_active' => 'boolean',
            'imap_port' => 'integer',
            'smtp_port' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
