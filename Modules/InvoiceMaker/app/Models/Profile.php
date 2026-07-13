<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class Profile extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_profiles';

    protected $guarded = [];

    protected $casts = [
        'enable_automated_reminders' => 'boolean',
        'late_fee_percentage' => 'decimal:2',
        'stripe_secret_key' => 'encrypted',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'team_id', 'team_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'team_id', 'team_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'team_id', 'team_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'team_id', 'team_id');
    }
}
