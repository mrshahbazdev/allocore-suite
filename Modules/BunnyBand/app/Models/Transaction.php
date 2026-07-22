<?php

namespace Modules\BunnyBand\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class Transaction extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_transactions';

    protected $fillable = [
        'team_id', 'bunnyband_profile_id', 'type', 'amount', 'status',
        'description', 'payment_method', 'payment_proof', 'screenshot',
        'bunnyband_withdrawal_method_id', 'processed_by', 'admin_note', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'processed_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BunnyBandProfile::class, 'bunnyband_profile_id');
    }

    public function withdrawalMethod(): BelongsTo
    {
        return $this->belongsTo(WithdrawalMethod::class, 'bunnyband_withdrawal_method_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
