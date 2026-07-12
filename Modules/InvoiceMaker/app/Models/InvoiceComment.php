<?php

namespace Modules\InvoiceMaker\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class InvoiceComment extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_invoice_comments';

    protected $guarded = [];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
