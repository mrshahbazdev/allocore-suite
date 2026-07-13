<?php

namespace Modules\FinancialPlatform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class PaypalTransaction extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_paypal_transactions';

    protected $fillable = [
        'team_id', 'user_id',
        'lead_id',
        'paypal_order_id',
        'payer_email',
        'payer_name',
        'amount',
        'currency',
        'status',
        'description',
        'paypal_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paypal_response' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
