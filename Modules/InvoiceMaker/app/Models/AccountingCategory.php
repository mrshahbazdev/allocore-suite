<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class AccountingCategory extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_accounting_categories';

    protected $guarded = [];
}
