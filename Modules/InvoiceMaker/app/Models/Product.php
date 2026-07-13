<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class Product extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_products';

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'manage_stock' => 'boolean',
        'stock_quantity' => 'decimal:2',
    ];

    public function decrementStock(float $quantity): void
    {
        if ($this->manage_stock) {
            $this->decrement('stock_quantity', $quantity);
        }
    }
}
