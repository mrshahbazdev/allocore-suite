<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class CashBookEntry extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_cash_book_entries';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'document_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($entry): void {
            if (! $entry->booking_number) {
                $entry->booking_number = static::generateBookingNumber($entry->team_id);
            }
        });
    }

    public static function generateBookingNumber($teamId): string
    {
        $profile = Profile::withoutGlobalScopes()->where('team_id', $teamId)->first();

        $prefix = $profile?->booking_number_prefix ?? 'BOOK';
        $next = $profile?->booking_number_next ?? 1;
        $year = now()->year;

        $number = $prefix.'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT).'-'.$year;
        while (static::withoutGlobalScopes()->where('team_id', $teamId)->where('booking_number', $number)->exists()) {
            $next++;
            $number = $prefix.'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT).'-'.$year;
        }

        $profile?->update(['booking_number_next' => $next + 1]);

        return $number;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountingCategory::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function business(): BelongsTo
    {
        return $this->profile();
    }
}
