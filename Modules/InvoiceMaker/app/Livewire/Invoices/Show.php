<?php

namespace Modules\InvoiceMaker\Livewire\Invoices;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Mail\InvoiceMail;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\EmailLog;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Modules\InvoiceMaker\Services\EmailTemplateService;
use Modules\InvoiceMaker\Services\MailConfigurationService;
use Modules\InvoiceMaker\Services\NetworkSyncService;
use Modules\InvoiceMaker\Services\PdfGenerationService;

#[Layout('layouts.shell')]
class Show extends Component
{
    public Invoice $invoice;

    public string $payment_amount = '';

    public string $payment_method = 'bank_transfer';

    public string $payment_date = '';

    public string $payment_notes = '';

    public float $total_expenses = 0;

    public float $profit = 0;

    public float $margin_percentage = 0;

    public bool $showEmailModal = false;

    public string $emailSubject = '';

    public string $emailBody = '';

    public bool $showPaidModal = false;

    public string $paymentSource = '';

    public string $paymentDescription = '';

    protected array $rules = [
        'payment_amount' => 'required|numeric|min:0.01',
        'payment_method' => 'required|in:bank_transfer,credit_card,cash,check,paypal,stripe',
        'payment_date' => 'required|date',
        'emailSubject' => 'nullable|string|max:255',
        'emailBody' => 'nullable|string',
    ];

    public function mount(Invoice $invoice): void
    {
        $this->authorize('view', $invoice);
        $this->invoice = $invoice->load(['items.product', 'payments', 'client', 'business', 'expenses']);
        $this->payment_date = now()->toDateString();
        $this->calculateProfitability();
    }

    public function calculateProfitability(): void
    {
        $this->total_expenses = (float) $this->invoice->expenses->sum('amount');
        $this->profit = (float) $this->invoice->grand_total - $this->total_expenses;
        $this->margin_percentage = $this->invoice->grand_total > 0
            ? ($this->profit / (float) $this->invoice->grand_total) * 100
            : 0;
    }

    public function markAsSent(): void
    {
        $this->invoice->update(['status' => Invoice::STATUS_SENT]);
        $this->invoice->deductInventory();
        NetworkSyncService::syncInvoiceToExpense($this->invoice);
        session()->flash('message', ($this->invoice->isEstimate() ? 'Estimate' : 'Invoice').' marked as sent.');
    }

    public function openEmailModal(): void
    {
        if (! $this->invoice->client->email) {
            session()->flash('error', 'Client does not have an email address.');

            return;
        }

        $client = $this->invoice->client;
        $business = $this->invoice->business;

        $templateService = new EmailTemplateService;
        $template = $templateService->getParsedTemplate($this->invoice, $this->invoice->isEstimate() ? 'invoice' : 'invoice'); // Treat estimates as invoices for templates for now

        $this->emailSubject = $template['subject'];
        $this->emailBody = $template['body'];

        $this->showEmailModal = true;
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
        $this->reset(['emailSubject', 'emailBody']);
    }

    public function sendEmail(PdfGenerationService $pdfService): void
    {
        if (! $this->invoice->client->email) {
            session()->flash('error', 'Client does not have an email address.');

            return;
        }

        $this->validate([
            'emailSubject' => 'required|string|max:255',
            'emailBody' => 'required|string',
        ]);

        $pdfContent = $pdfService->generate($this->invoice);

        try {
            MailConfigurationService::getMailer($this->invoice->business)
                ->to($this->invoice->client->email)
                ->send(new InvoiceMail($this->invoice, $pdfContent, $this->emailSubject, $this->emailBody));

            EmailLog::create([
                'invoice_id' => $this->invoice->id,
                'team_id' => $this->invoice->team_id,
                'recipient_email' => $this->invoice->client->email,
                'subject' => $this->emailSubject,
                'type' => EmailLog::TYPE_MANUAL,
                'status' => EmailLog::STATUS_SENT,
            ]);
        } catch (\Exception $e) {
            EmailLog::create([
                'invoice_id' => $this->invoice->id,
                'team_id' => $this->invoice->team_id,
                'recipient_email' => $this->invoice->client->email,
                'subject' => $this->emailSubject,
                'type' => EmailLog::TYPE_MANUAL,
                'status' => EmailLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            $this->closeEmailModal();
            session()->flash('error', __('Failed to send email: ').$e->getMessage());

            return;
        }

        if ($this->invoice->status === Invoice::STATUS_DRAFT) {
            $this->invoice->update(['status' => Invoice::STATUS_SENT]);
            $this->invoice->deductInventory();
            NetworkSyncService::syncInvoiceToExpense($this->invoice);
        }

        $this->closeEmailModal();
        session()->flash('message', ($this->invoice->isEstimate() ? 'Estimate' : 'Invoice').' emailed successfully to '.$this->invoice->client->email);
    }

    public function convertToInvoice(): void
    {
        if (! $this->invoice->isEstimate()) {
            return;
        }

        $this->invoice->update([
            'type' => Invoice::TYPE_INVOICE,
            'status' => Invoice::STATUS_DRAFT,
        ]);

        session()->flash('message', 'Estimate converted to Invoice successfully.');
    }

    public function openPaidModal(): void
    {
        $this->payment_date = now()->format('Y-m-d');
        $this->paymentDescription = __('Payment for Invoice').' '.$this->invoice->invoice_number;
        $this->showPaidModal = true;
    }

    public function closePaidModal(): void
    {
        $this->showPaidModal = false;
        $this->reset(['paymentSource', 'paymentDescription']);
    }

    public function markAsPaid(): void
    {
        $this->validate([
            'payment_date' => 'required|date',
            'paymentSource' => 'required|in:cash,bank',
            'paymentDescription' => 'required|string|max:255',
        ]);

        DB::transaction(function () {
            // Update Invoice Status
            $this->invoice->update([
                'status' => Invoice::STATUS_PAID,
                'amount_paid' => $this->invoice->grand_total,
                'amount_due' => 0,
            ]);

            // Create Payment History record
            Payment::create([
                'invoice_id' => $this->invoice->id,
                'amount' => $this->invoice->grand_total,
                'method' => $this->paymentSource === 'cash' ? 'cash' : 'bank_transfer',
                'date' => $this->payment_date,
                'notes' => $this->paymentDescription,
            ]);

            // Create Cash Book Entry
            CashBookEntry::create([
                'team_id' => $this->invoice->team_id,
                'date' => $this->payment_date,
                'document_date' => $this->invoice->invoice_date,
                'amount' => $this->invoice->grand_total,
                'type' => 'income',
                'source' => $this->paymentSource,
                'description' => $this->paymentDescription,
                'partner_name' => $this->invoice->client->company_name ?? $this->invoice->client->name,
                'reference_number' => $this->invoice->invoice_number,
                'invoice_id' => $this->invoice->id,
            ]);

            $this->invoice->deductInventory();
        });

        $this->closePaidModal();
        session()->flash('message', __('Invoice marked as paid and Cash Book entry created.'));
    }

    public function markAsOverdue(): void
    {
        $this->invoice->update(['status' => Invoice::STATUS_OVERDUE]);
        session()->flash('message', 'Invoice marked as overdue.');
    }

    public function cancelInvoice(): void
    {
        $wasPaid = $this->invoice->status === Invoice::STATUS_PAID;

        DB::transaction(function () use ($wasPaid) {
            if ($wasPaid) {
                // Remove payments and associated cash book entries
                $this->invoice->payments()->delete();
                CashBookEntry::where('invoice_id', $this->invoice->id)->delete();

                $this->invoice->update([
                    'status' => Invoice::STATUS_CANCELLED,
                    'amount_paid' => 0,
                    'amount_due' => $this->invoice->grand_total,
                ]);
            } else {
                $this->invoice->update(['status' => Invoice::STATUS_CANCELLED]);
            }
        });

        $this->invoice->load('payments');
        session()->flash('message', ($this->invoice->isEstimate() ? 'Estimate' : 'Invoice').' cancelled.');
    }

    public function reopenInvoice(): void
    {
        DB::transaction(function () {
            // Remove payments and associated cash book entries
            $this->invoice->payments()->delete();
            CashBookEntry::where('invoice_id', $this->invoice->id)->delete();

            $this->invoice->update([
                'status' => Invoice::STATUS_SENT,
                'amount_paid' => 0,
                'amount_due' => $this->invoice->grand_total,
            ]);
        });

        $this->invoice->load('payments');
        session()->flash('message', __('Invoice reopened. Payments and cash book entries have been reversed.'));
    }

    public function markAsUnpaid(): void
    {
        $this->invoice->update(['status' => Invoice::STATUS_SENT]);
        session()->flash('message', ($this->invoice->isEstimate() ? 'Estimate' : 'Invoice').' marked as unpaid.');
    }

    public function recordPayment(): void
    {
        $this->validate();

        $amount = (float) $this->payment_amount;

        if ($amount > $this->invoice->amount_due) {
            session()->flash('error', 'Payment amount cannot exceed due amount.');

            return;
        }

        DB::transaction(function () use ($amount) {
            Payment::create([
                'invoice_id' => $this->invoice->id,
                'amount' => $amount,
                'method' => $this->payment_method,
                'date' => $this->payment_date,
                'notes' => $this->payment_notes,
            ]);

            // Create Cash Book Entry for partial payment
            CashBookEntry::create([
                'team_id' => $this->invoice->team_id,
                'date' => $this->payment_date,
                'document_date' => $this->invoice->invoice_date,
                'amount' => $amount,
                'type' => 'income',
                'source' => in_array($this->payment_method, ['cash']) ? 'cash' : 'bank',
                'description' => $this->payment_notes ?: __('Partial payment for').' '.$this->invoice->invoice_number,
                'partner_name' => $this->invoice->client->company_name ?? $this->invoice->client->name,
                'reference_number' => $this->invoice->invoice_number,
                'invoice_id' => $this->invoice->id,
            ]);

            $totalPaid = $this->invoice->payments->sum('amount') + $amount;
            $this->invoice->update([
                'amount_paid' => $totalPaid,
                'amount_due' => $this->invoice->grand_total - $totalPaid,
            ]);

            if ($this->invoice->amount_due <= 0) {
                $this->invoice->update(['status' => Invoice::STATUS_PAID]);
                $this->invoice->deductInventory();
            }
        });

        $this->payment_amount = '';
        $this->payment_notes = '';
        $this->invoice->load('payments');
        session()->flash('message', __('Payment recorded and Cash Book entry created.'));
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = $this->invoice->payments()->findOrFail($paymentId);
        $payment->delete();

        $totalPaid = $this->invoice->payments->sum('amount');
        $this->invoice->update([
            'amount_paid' => $totalPaid,
            'amount_due' => $this->invoice->grand_total - $totalPaid,
        ]);

        if ($totalPaid < $this->invoice->grand_total) {
            $this->invoice->update(['status' => Invoice::STATUS_SENT]);
        }

        $this->invoice->load('payments');
        session()->flash('message', 'Payment deleted successfully.');
    }

    private function getDefaultEmailSubject(string $lang): string
    {
        $subjects = [
            'en' => 'Invoice {invoice_number} from {business_name}',
            'de' => 'Ihre Rechnung {invoice_number} von {business_name}',
            'fr' => 'Votre facture {invoice_number} de {business_name}',
            'es' => 'Su factura {invoice_number} de {business_name}',
            'ar' => 'فاتورة {invoice_number} من {business_name}',
            'nl' => 'Uw factuur {invoice_number} van {business_name}',
            'pl' => 'Twoja faktura {invoice_number} od {business_name}',
            'it' => 'La tua fattura {invoice_number} da {business_name}',
            'hi' => '{business_name} से इनवॉइस {invoice_number}',
            'zh' => '来自 {business_name} 的发票 {invoice_number}',
        ];

        return $subjects[$lang] ?? $subjects['en'];
    }

    private function getDefaultEmailTemplate(string $lang): string
    {
        $templates = [
            'en' => "Hello {client_name},\n\nHere is your new invoice {invoice_number}.\n\nThe outstanding amount of {amount_due} is due by {due_date}.\n\nYou can view and download your invoice online here:\n{public_link}\n\nThank you for your business!\n\nBest regards,\n{business_name}",

            'de' => "Hallo {client_name},\n\nanbei erhalten Sie Ihre neue Rechnung {invoice_number}.\n\nDer offene Betrag in Höhe von {amount_due} ist bis zum {due_date} fällig.\n\nSie können die Rechnung auch online einsehen und herunterladen:\n{public_link}\n\nVielen Dank für Ihr Vertrauen!\n\nMit freundlichen Grüßen,\n{business_name}",

            'fr' => "Bonjour {client_name},\n\nVeuillez trouver ci-joint votre nouvelle facture {invoice_number}.\n\nLe montant de {amount_due} est dû pour le {due_date}.\n\nVous pouvez consulter et télécharger votre facture en ligne ici :\n{public_link}\n\nMerci pour votre confiance !\n\nCordialement,\n{business_name}",

            'es' => "Hola {client_name},\n\nAdjunto encontrará su nueva factura {invoice_number}.\n\nEl monto pendiente de {amount_due} vence el {due_date}.\n\nPuede ver y descargar su factura en línea aquí:\n{public_link}\n\n¡Gracias por hacer negocios con nosotros!\n\nAtentamente,\n{business_name}",

            'ar' => "مرحبًا {client_name}،\n\nإليك فاتورتك الجديدة {invoice_number}.\n\nالمبلغ المستحق {amount_due} مستحق الدفع بحلول {due_date}.\n\nيمكنك عرض وتنزيل فاتورتك عبر الإنترنت هنا:\n{public_link}\n\nشكرًا لتعاملك معنا!\n\nمع أطيب التحيات،\n{business_name}",

            'nl' => "Hallo {client_name},\n\nHierbij ontvangt u uw nieuwe factuur {invoice_number}.\n\nHet openstaande bedrag van {amount_due} dient betaald te worden voor {due_date}.\n\nU kunt uw factuur hier online bekijken en downloaden:\n{public_link}\n\nBedankt voor uw vertrouwen!\n\nMet vriendelijke groet,\n{business_name}",

            'pl' => "Dzień dobry {client_name},\n\nW załączniku przesyłamy nową fakturę {invoice_number}.\n\nKwota do zapłaty w wysokości {amount_due} jest wymagalna do {due_date}.\n\nFakturę można również sprawdzić i pobrać online tutaj:\n{public_link}\n\nDziękujemy za współpracę!\n\nZ poważaniem,\n{business_name}",

            'it' => "Gentile {client_name},\n\nIn allegato trovate la vostra nuova fattura {invoice_number}.\n\nL'importo in sospeso di {amount_due} scade il {due_date}.\n\nPotete visualizzare e scaricare la fattura online qui:\n{public_link}\n\nGrazie per aver scelto i nostri servizi!\n\nCordiali saluti,\n{business_name}",

            'hi' => "नमस्ते {client_name},\n\nयहाँ आपका नया इनवॉइस {invoice_number} है।\n\n{amount_due} की बकाया राशि {due_date} तक देय है।\n\nआप यहाँ अपना इनवॉइस ऑनलाइन देख और डाउनलोड कर सकते हैं:\n{public_link}\n\nआपके व्यवसाय के लिए धन्यवाद!\n\nसादर,\n{business_name}",

            'zh' => "您好 {client_name}，\n\n这是您的新发票 {invoice_number}。\n\n未结金额 {amount_due} 需要在 {due_date} 之前支付。\n\n您可以在此处在线查看和下载您的发票：\n{public_link}\n\n感谢您对我们业务的支持！\n\n此致，\n{business_name}",
        ];

        return $templates[$lang] ?? $templates['en'];
    }

    public function render()
    {
        return view('invoicemaker::livewire.invoices.show');
    }
}
