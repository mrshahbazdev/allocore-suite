@extends('layouts.shell')

@section('title', 'PayPal-Einstellungen — Allocore')
@section('page-title', 'PayPal-Einstellungen')

@section('topbar-actions')
    <a href="{{ route('paypal.index') }}" class="btn btn-secondary btn-sm">{{ __('Zurück') }}</a>
@endsection

@push('styles')
<style>
    .settings-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
    .info-card { font-size: 13px; color: #94a3b8; line-height: 1.6; }
    .info-card h4 { color: #0f172a; font-size: 14px; margin-bottom: 8px; }
    .info-card ul { padding-left: 16px; margin-top: 8px; }
    .info-card li { margin-bottom: 6px; }
    .info-card a { color: #818cf8; text-decoration: none; }
    .info-card a:hover { text-decoration: underline; }
    @media (max-width: 900px) { .settings-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="settings-grid">
    <div class="card">
        <div class="card-title">{{ __('PayPal API-Konfiguration') }}</div>

        @php
            $configPath = storage_path('app/paypal_config.json');
            $existing = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : null;
        @endphp

        <form method="POST" action="{{ route('paypal.save-settings') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">{{ __('Modus') }}</label>
                <select name="paypal_mode" class="form-control" style="max-width:300px;">
                    <option value="sandbox" {{ old('paypal_mode', $existing['mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>{{ __('Sandbox (Test)') }}</option>
                    <option value="live" {{ old('paypal_mode', $existing['mode'] ?? '') === 'live' ? 'selected' : '' }}>{{ __('Live (Produktion)') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Client ID *') }}</label>
                <input type="text" name="paypal_client_id" class="form-control" required
                    value="{{ old('paypal_client_id', $existing['client_id'] ?? '') }}"
                    placeholder="{{ __('PayPal Client ID eingeben') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Client Secret *') }}</label>
                <input type="password" name="paypal_client_secret" class="form-control" required
                    value="{{ old('paypal_client_secret', $existing['client_secret'] ?? '') }}"
                    placeholder="{{ __('PayPal Client Secret eingeben') }}">
            </div>

            @if($existing)
                <div style="font-size:12px; color:#64748b; margin-bottom:16px;">
                    Zuletzt aktualisiert: {{ $existing['updated_at'] ?? '—' }}
                </div>
            @endif

            <button type="submit" class="btn btn-primary">{{ __('Einstellungen speichern') }}</button>
        </form>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card info-card">
            <h4>{{ __('Einrichtung') }}</h4>
            <p>{{ __('So erhalten Sie Ihre PayPal API-Zugangsdaten:') }}</p>
            <ul>
                <li>{{ __('Melden Sie sich bei') }}<a href="https://developer.paypal.com" target="_blank">{{ __('developer.paypal.com') }}</a>{{ __('an') }}</li>
                <li>{{ __('Erstellen Sie eine neue App unter') }}<strong>{{ __('Apps & Credentials') }}</strong></li>
                <li>{{ __('Kopieren Sie') }}<strong>{{ __('Client ID') }}</strong>{{ __('und') }}<strong>{{ __('Secret') }}</strong></li>
                <li>{{ __('Verwenden Sie den') }}<strong>{{ __('Sandbox-Modus') }}</strong>{{ __('zum Testen') }}</li>
            </ul>
        </div>

        <div class="card info-card">
            <h4>{{ __('Unterstützte Funktionen') }}</h4>
            <ul>
                <li>{{ __('PayPal Checkout-Zahlungen') }}</li>
                <li>{{ __('Sandbox- und Live-Modus') }}</li>
                <li>{{ __('Lead-verknüpfte Zahlungen') }}</li>
                <li>{{ __('Transaktionsverlauf') }}</li>
                <li>{{ __('Automatische Erfassung') }}</li>
            </ul>
        </div>

        <div class="card info-card">
            <h4>{{ __('Währungen') }}</h4>
            <p>{{ __('EUR, USD, GBP und CHF werden unterstützt.') }}</p>
        </div>
    </div>
</div>
@endsection
