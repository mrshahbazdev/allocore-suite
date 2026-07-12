<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class EmailLog extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_email_logs';

    protected $guarded = [];
}
