<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class Template extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_templates';

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'show_tax' => 'boolean',
        'show_discount' => 'boolean',
    ];
}
