<?php

namespace Modules\InvoiceMaker\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Services\PdfGenerationService;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice, PdfGenerationService $pdf)
    {
        return $pdf->download($invoice);
    }

    public function preview(Invoice $invoice, PdfGenerationService $pdf)
    {
        return response($pdf->output($invoice), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }
}
