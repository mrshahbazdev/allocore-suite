<?php

namespace Modules\InvoiceMaker\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\InvoiceMaker\Models\Invoice;

class PdfGenerationService
{
    public function output(Invoice $invoice): string
    {
        $invoice->loadMissing(['client', 'items.product', 'template', 'profile']);

        return Pdf::loadView('invoicemaker::invoices.pdf', compact('invoice'))->output();
    }

    public function download(Invoice $invoice)
    {
        $label = $invoice->isEstimate() ? __('Estimate') : __('Invoice');

        return response()->streamDownload(
            fn () => print $this->output($invoice),
            "{$label}-{$invoice->invoice_number}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }
}
