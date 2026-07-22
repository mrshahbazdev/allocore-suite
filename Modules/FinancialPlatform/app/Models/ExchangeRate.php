<?php

namespace Modules\FinancialPlatform\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'financial_exchange_rates';

    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'date' => 'date',
        ];
    }

    public static function convert(float $amount, string $from, string $to, ?\DateTimeInterface $date = null): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = static::query()
            ->where('from_currency', $from)
            ->where('to_currency', $to)
            ->when($date, fn ($query) => $query->whereDate('date', '<=', $date))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('rate');

        if ($rate === null) {
            throw new \RuntimeException("No exchange rate found from {$from} to {$to}.");
        }

        return $amount * (float) $rate;
    }
}
