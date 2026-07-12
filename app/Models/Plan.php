<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly', 'price_yearly', 'currency',
        'stripe_price_id_monthly', 'stripe_price_id_yearly',
        'paypal_plan_id_monthly', 'paypal_plan_id_yearly',
        'billable_scope', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
    ];

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)->withTimestamps();
    }

    public function toolSubscriptions(): HasMany
    {
        return $this->hasMany(ToolSubscription::class);
    }

    public function priceFor(string $interval): string
    {
        return $interval === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }
}
