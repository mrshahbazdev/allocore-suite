<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class EmailTemplate extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_email_templates';

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
