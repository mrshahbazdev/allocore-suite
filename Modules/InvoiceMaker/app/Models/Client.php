<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class Client extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_clients';

    protected $guarded = [];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
