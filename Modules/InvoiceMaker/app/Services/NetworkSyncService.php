<?php

namespace Modules\InvoiceMaker\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;

class NetworkSyncService
{
    /**
     * Syncs an incoming invoice directly into the receiving client's Profile Expenses.
     */
    public static function syncInvoiceToExpense(Invoice $invoice)
    {
        try {
            // 1. Ensure the invoice has a linked User
            if (! $invoice->client || ! $invoice->client->user_id) {
                return false;
            }

            // 2. Fetch the User and check if they own a Profile
            $receiverUser = User::with('ownedBusiness')->find($invoice->client->user_id);

            if (! $receiverUser || ! $receiverUser->ownedBusiness) {
                return false; // Not a business owner on the platform
            }

            $receiverBusiness = $receiverUser->ownedBusiness;

            // 3. Ensure they aren't billing themselves (edge case, but good to check)
            if ($invoice->team_id === $receiverBusiness->id) {
                return false;
            }

            // 4. Ensure the receiving business has opted-in to accept network invoices
            if (! $receiverBusiness->accept_network_invoices) {
                return false;
            }

            // 4. Upsert an Expense record in the receiver's business
            Expense::updateOrCreate(
                [
                    'team_id' => $receiverBusiness->id,
                    'network_invoice_id' => $invoice->id,
                ],
                [
                    'category' => 'Network Invoice',
                    'amount' => $invoice->grand_total,
                    'date' => $invoice->invoice_date,
                    'partner_name' => $invoice->business->name, // Sender
                    'reference_number' => $invoice->invoice_number,
                    'description' => 'Auto-synced from Invoice #'.$invoice->invoice_number,
                ]
            );

            Log::info("Successfully synced Invoice #{$invoice->id} to Profile #{$receiverBusiness->id} Expenses.");

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to sync invoice to network expense: '.$e->getMessage());

            return false;
        }
    }
}
