<?php

namespace Modules\InvoiceMaker\Services;

use Illuminate\Support\Facades\DB;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Profile;

class InvoiceNumberService
{
    public function generate(Profile $profile, string $type = Invoice::TYPE_INVOICE): string
    {
        return DB::transaction(function () use ($profile, $type): string {
            $lockedProfile = Profile::withoutGlobalScopes()
                ->whereKey($profile->id)
                ->lockForUpdate()
                ->firstOrFail();
            $prefixField = $type === Invoice::TYPE_ESTIMATE
                ? 'estimate_number_prefix'
                : 'invoice_number_prefix';
            $nextField = $type === Invoice::TYPE_ESTIMATE
                ? 'estimate_number_next'
                : 'invoice_number_next';
            $prefix = $lockedProfile->{$prefixField};
            $next = $lockedProfile->{$nextField};

            do {
                $number = sprintf('%s-%04d', $prefix, $next++);
            } while (Invoice::withoutGlobalScopes()
                ->where('team_id', $lockedProfile->team_id)
                ->where('invoice_number', $number)
                ->exists());

            $lockedProfile->update([$nextField => $next]);

            return $number;
        });
    }
}
