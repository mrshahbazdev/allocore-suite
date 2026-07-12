<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    public function isConfigured(): bool
    {
        return (bool) (config('services.paypal.client_id') && config('services.paypal.client_secret'));
    }

    public function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function accessToken(): ?string
    {
        $response = Http::asForm()
            ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.client_secret'))
            ->timeout(15)
            ->post($this->baseUrl().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        return $response->successful() ? $response->json('access_token') : null;
    }

    /**
     * Create a PayPal order for a plan purchase. Returns [orderId, approveUrl] or null.
     */
    public function createOrder(Plan $plan, string $interval, string $returnUrl, string $cancelUrl): ?array
    {
        $token = $this->accessToken();
        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => strtoupper($plan->currency),
                        'value' => number_format((float) $plan->priceFor($interval), 2, '.', ''),
                    ],
                    'description' => $plan->name.' ('.$interval.')',
                ]],
                'application_context' => [
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                    'brand_name' => config('app.name'),
                ],
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $approveLink = collect($data['links'] ?? [])->firstWhere('rel', 'approve');

        return $approveLink ? ['order_id' => $data['id'], 'approve_url' => $approveLink['href']] : null;
    }

    public function captureOrder(string $orderId): bool
    {
        $token = $this->accessToken();
        if (! $token) {
            return false;
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->post($this->baseUrl()."/v2/checkout/orders/{$orderId}/capture");

        return $response->successful();
    }
}
