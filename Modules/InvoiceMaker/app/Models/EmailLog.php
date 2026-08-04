<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class EmailLog extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_email_logs';

    protected $guarded = [];

    public const TYPE_MANUAL = 'manual';

    public const TYPE_SCHEDULED = 'scheduled';

    public const TYPE_REMINDER = 'reminder';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function business(): BelongsTo
    {
        return $this->profile();
    }
}
