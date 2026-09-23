<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class AffiliateClick extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_affiliate_clicks';

    protected $fillable = [
        'team_id',
        'book_id',
        'user_id',
        'referrer_url',
        'source_type',
        'source_id',
        'ip_address',
        'user_agent',
        'is_converted',
        'commission_amount',
    ];

    protected $casts = [
        'is_converted' => 'boolean',
        'commission_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
