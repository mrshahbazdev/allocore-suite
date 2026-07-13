<?php

namespace Modules\InvoiceMaker\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Services\PdfGenerationService;

class PublicInvoiceController extends Controller
{
    public function show(string $uuid)
    {
        $invoice = $this->invoice($uuid);
        $invoice->update(['public_viewed_at' => $invoice->public_viewed_at ?? now()]);

        return view('invoicemaker::invoices.public', compact('invoice'));
    }

    public function download(string $uuid, PdfGenerationService $pdf)
    {
        return $pdf->download($this->invoice($uuid));
    }

    public function approve(string $uuid)
    {
        $invoice = $this->invoice($uuid);
        abort_unless($invoice->isEstimate(), 404);
        $invoice->update([
            'status' => Invoice::STATUS_SENT,
            'accepted_at' => now(),
        ]);

        return back()->with('success', __('Estimate approved.'));
    }

    public function requestRevision(Request $request, string $uuid)
    {
        $data = $request->validate(['comment' => ['required', 'string', 'max:5000']]);
        $invoice = $this->invoice($uuid);
        abort_unless($invoice->isEstimate(), 404);
        $invoice->update(['revision_requested_at' => now()]);
        $invoice->comments()->create([
            'team_id' => $invoice->team_id,
            'author_name' => $invoice->client->name,
            'comment' => $data['comment'],
            'is_internal' => false,
        ]);

        return back()->with('success', __('Revision requested.'));
    }

    public function comment(Request $request, string $uuid)
    {
        $data = $request->validate(['comment' => ['required', 'string', 'max:5000']]);
        $invoice = $this->invoice($uuid);
        $invoice->comments()->create([
            'team_id' => $invoice->team_id,
            'author_name' => $invoice->client->name,
            'comment' => $data['comment'],
            'is_internal' => false,
        ]);

        return back()->with('success', __('Comment added.'));
    }

    private function invoice(string $uuid): Invoice
    {
        return Invoice::withoutGlobalScopes()
            ->with([
                'client' => fn ($query) => $query->withoutGlobalScopes(),
                'items' => fn ($query) => $query->withoutGlobalScopes(),
                'items.product' => fn ($query) => $query->withoutGlobalScopes(),
                'payments' => fn ($query) => $query->withoutGlobalScopes(),
                'comments' => fn ($query) => $query->withoutGlobalScopes(),
                'profile' => fn ($query) => $query->withoutGlobalScopes(),
                'template' => fn ($query) => $query->withoutGlobalScopes(),
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
