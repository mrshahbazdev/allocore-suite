<?php

namespace Modules\InvoiceMaker\Livewire\Documents;

use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Services\AccountingService;

#[Layout('layouts.shell')]
class Show extends Component
{
    public Invoice $invoice;

    public string $payment_amount = '';

    public string $payment_method = 'bank_transfer';

    public string $payment_date = '';

    public ?string $payment_notes = null;

    public string $comment = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->payment_amount = (string) $invoice->amount_due;
        $this->payment_date = today()->toDateString();
    }

    public function markSent(): void
    {
        $this->invoice->update([
            'status' => Invoice::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function addPayment(AccountingService $accounting): void
    {
        $data = $this->validate([
            'payment_amount' => ['required', 'numeric', 'gt:0', 'max:'.$this->invoice->amount_due],
            'payment_method' => ['required', 'in:bank_transfer,credit_card,cash,check,paypal,stripe'],
            'payment_date' => ['required', 'date'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        $accounting->recordPayment(
            $this->invoice,
            (float) $data['payment_amount'],
            $data['payment_method'],
            $data['payment_date'],
            $data['payment_notes'],
        );
        $this->invoice->refresh();
        $this->payment_amount = (string) $this->invoice->amount_due;
        session()->flash('success', __('Payment recorded.'));
    }

    public function addComment(): void
    {
        $this->validate(['comment' => ['required', 'string', 'max:5000']]);
        $this->invoice->comments()->create([
            'team_id' => $this->invoice->team_id,
            'user_id' => auth()->id(),
            'author_name' => auth()->user()->name,
            'comment' => $this->comment,
            'is_internal' => true,
        ]);
        $this->reset('comment');
    }

    public function render()
    {
        $this->invoice->load(['client', 'items.product', 'payments', 'comments.user', 'profile']);
        $publicUrl = URL::temporarySignedRoute(
            'invoicemaker.public.show',
            now()->addDays(30),
            ['uuid' => $this->invoice->uuid],
        );

        return view('invoicemaker::livewire.documents.show', compact('publicUrl'));
    }
}
