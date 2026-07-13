<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\FinancialPlatform\Models\PaypalTransaction;
use Modules\FinancialPlatform\Models\Setting;

class PaypalController extends Controller
{
    public function index(Request $request)
    {
        $query = PaypalTransaction::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->with('lead')->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_amount' => PaypalTransaction::query()->where('status', 'completed')->sum('amount'),
            'total_count' => PaypalTransaction::query()->where('status', 'completed')->count(),
            'pending' => PaypalTransaction::query()->where('status', 'pending')->count(),
        ];

        return view('financialplatform::paypal.index', compact('transactions', 'stats'));
    }

    public function settings()
    {
        return view('financialplatform::paypal.settings', [
            'config' => $this->getConfig(),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'paypal_mode' => 'required|in:sandbox,live',
            'paypal_client_id' => 'required|string|max:500',
            'paypal_client_secret' => 'required|string|max:500',
        ]);

        Setting::set('paypal_mode', $request->paypal_mode);
        Setting::set('paypal_client_id', $request->paypal_client_id);
        Setting::set('paypal_client_secret', $request->paypal_client_secret);

        return redirect()->route('paypal.settings')->with('success', 'PayPal-Einstellungen gespeichert.');
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
            'lead_id' => 'nullable|exists:financial_leads,id',
        ]);

        $config = $this->getConfig();
        if (! $config) {
            return back()->with('error', 'PayPal ist noch nicht konfiguriert. Bitte zuerst Einstellungen speichern.');
        }

        $accessToken = $this->getAccessToken($config);
        if (! $accessToken) {
            return back()->with('error', 'PayPal-Authentifizierung fehlgeschlagen. Bitte Zugangsdaten prüfen.');
        }

        $baseUrl = $config['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $orderId = 'ALC-'.Str::upper(Str::random(12));

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->post("{$baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $orderId,
                    'amount' => [
                        'currency_code' => strtoupper($request->currency),
                        'value' => number_format($request->amount, 2, '.', ''),
                    ],
                    'description' => $request->description ?? 'Allocore Payment',
                ]],
                'application_context' => [
                    'return_url' => route('paypal.capture'),
                    'cancel_url' => route('paypal.cancel'),
                    'brand_name' => 'Allocore Financial Platform',
                ],
            ]);

        if (! $response->successful()) {
            return back()->with('error', 'PayPal-Bestellung konnte nicht erstellt werden.');
        }

        $orderData = $response->json();

        PaypalTransaction::create([
            'lead_id' => $request->lead_id,
            'paypal_order_id' => $orderData['id'],
            'amount' => $request->amount,
            'currency' => strtoupper($request->currency),
            'status' => 'pending',
            'description' => $request->description,
            'paypal_response' => $orderData,
        ]);

        $approveLink = collect($orderData['links'] ?? [])->firstWhere('rel', 'approve');

        if ($approveLink) {
            return redirect()->away($approveLink['href']);
        }

        return back()->with('error', 'PayPal-Genehmigungslink nicht gefunden.');
    }

    public function capture(Request $request)
    {
        $paypalOrderId = $request->query('token');

        if (! $paypalOrderId) {
            return redirect()->route('paypal.index')->with('error', 'Ungültige PayPal-Antwort.');
        }

        $transaction = PaypalTransaction::query()->where('paypal_order_id', $paypalOrderId)->first();
        if (! $transaction) {
            return redirect()->route('paypal.index')->with('error', 'Transaktion nicht gefunden.');
        }

        $config = $this->getConfig();
        $accessToken = $this->getAccessToken($config);

        if (! $config || ! $accessToken) {
            return redirect()->route('paypal.index')->with('error', 'PayPal-Zugangsdaten fehlen.');
        }

        $baseUrl = $config['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->post("{$baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture");

        if ($response->successful()) {
            $captureData = $response->json();
            $payer = $captureData['payer'] ?? [];

            $transaction->update([
                'status' => 'completed',
                'payer_email' => $payer['email_address'] ?? null,
                'payer_name' => trim(($payer['name']['given_name'] ?? '').' '.($payer['name']['surname'] ?? '')),
                'paypal_response' => $captureData,
            ]);

            return redirect()->route('paypal.index')->with('success', 'Zahlung erfolgreich abgeschlossen!');
        }

        $transaction->update(['status' => 'failed', 'paypal_response' => $response->json()]);

        return redirect()->route('paypal.index')->with('error', 'Zahlung konnte nicht erfasst werden.');
    }

    public function cancel()
    {
        return redirect()->route('paypal.index')->with('error', 'Zahlung wurde abgebrochen.');
    }

    public function show(PaypalTransaction $transaction)
    {
        $transaction->load('lead');

        return view('financialplatform::paypal.show', compact('transaction'));
    }

    private function getConfig(): ?array
    {
        $mode = Setting::get('paypal_mode');
        $clientId = Setting::get('paypal_client_id');
        $clientSecret = Setting::get('paypal_client_secret');

        if (! $mode || ! $clientId || ! $clientSecret) {
            return null;
        }

        return [
            'mode' => $mode,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];
    }

    private function getAccessToken(?array $config): ?string
    {
        if (! $config) {
            return null;
        }

        $baseUrl = $config['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $response = Http::asForm()
            ->withBasicAuth($config['client_id'], $config['client_secret'])
            ->timeout(15)
            ->post("{$baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        return null;
    }
}
