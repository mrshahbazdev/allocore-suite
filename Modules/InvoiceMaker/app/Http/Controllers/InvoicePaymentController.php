<?php

namespace Modules\InvoiceMaker\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Modules\InvoiceMaker\Services\AccountingService;
use Stripe\StripeClient;

class InvoicePaymentController extends Controller
{
    public function checkout(string $uuid)
    {
        $invoice = $this->invoice($uuid);
        $profile = $invoice->profile;
        abort_unless($profile?->stripe_secret_key && $invoice->amount_due > 0, 404);

        $stripe = new StripeClient($profile->stripe_secret_key);
        $successUrl = route(
            'invoicemaker.public.payment.success',
            ['uuid' => $invoice->uuid],
        ).'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = URL::temporarySignedRoute(
            'invoicemaker.public.show',
            now()->addHour(),
            ['uuid' => $invoice->uuid],
        );
        $parameters = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => ['invoice_uuid' => $invoice->uuid],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($invoice->currency),
                    'unit_amount' => (int) round((float) $invoice->amount_due * 100),
                    'product_data' => [
                        'name' => __('Invoice :number', ['number' => $invoice->invoice_number]),
                    ],
                ],
            ]],
        ];

        if ($invoice->client->email) {
            $parameters['customer_email'] = $invoice->client->email;
        }

        $session = $stripe->checkout->sessions->create($parameters);

        return redirect()->away($session->url);
    }

    public function success(Request $request, string $uuid, AccountingService $accounting)
    {
        $request->validate(['session_id' => ['required', 'string']]);
        $invoice = $this->invoice($uuid);
        abort_unless($invoice->profile?->stripe_secret_key, 404);
        $stripe = new StripeClient($invoice->profile->stripe_secret_key);
        $session = $stripe->checkout->sessions->retrieve($request->string('session_id')->toString());

        abort_unless(
            $session->payment_status === 'paid'
            && ($session->metadata->invoice_uuid ?? null) === $invoice->uuid,
            422,
        );

        if (! Payment::withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->where('method', 'stripe')
            ->where('reference', $session->payment_intent)
            ->exists()) {
            $accounting->recordPayment(
                $invoice,
                (float) $session->amount_total / 100,
                'stripe',
                today()->toDateString(),
                __('Stripe payment'),
            )->update(['reference' => $session->payment_intent]);
        }

        return redirect(URL::temporarySignedRoute(
            'invoicemaker.public.show',
            now()->addDays(30),
            ['uuid' => $invoice->uuid],
        ))->with('success', __('Payment received.'));
    }

    private function invoice(string $uuid): Invoice
    {
        return Invoice::withoutGlobalScopes()
            ->with([
                'client' => fn ($query) => $query->withoutGlobalScopes(),
                'profile' => fn ($query) => $query->withoutGlobalScopes(),
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
