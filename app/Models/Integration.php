<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Integration extends Model
{
    protected $fillable = [
        'name',
        'type',
        'config',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }
}
