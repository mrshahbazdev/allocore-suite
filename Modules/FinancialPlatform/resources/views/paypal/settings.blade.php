@extends('layouts.shell')

@section('title', 'PayPal-Einstellungen — Allocore')
@section('page-title', 'PayPal-Einstellungen')

@section('topbar-actions')
    <a href="{{ route('paypal.index') }}" class="btn btn-secondary btn-sm">Zurück</a>
@endsection

@push('styles')
<style>
    .settings-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
    .info-card { font-size: 13px; color: #94a3b8; line-height: 1.6; }
    .info-card h4 { color: #c7d2fe; font-size: 14px; margin-bottom: 8px; }
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
        <div class="card-title">PayPal API-Konfiguration</div>

        @php
            $configPath = storage_path('app/paypal_config.json');
            $existing = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : null;
        @endphp

        <form method="POST" action="{{ route('paypal.save-settings') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Modus</label>
                <select name="paypal_mode" class="form-control" style="max-width:300px;">
                    <option value="sandbox" {{ old('paypal_mode', $existing['mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Test)</option>
                    <option value="live" {{ old('paypal_mode', $existing['mode'] ?? '') === 'live' ? 'selected' : '' }}>Live (Produktion)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Client ID *</label>
                <input type="text" name="paypal_client_id" class="form-control" required
                    value="{{ old('paypal_client_id', $existing['client_id'] ?? '') }}"
                    placeholder="PayPal Client ID eingeben">
            </div>

            <div class="form-group">
                <label class="form-label">Client Secret *</label>
                <input type="password" name="paypal_client_secret" class="form-control" required
                    value="{{ old('paypal_client_secret', $existing['client_secret'] ?? '') }}"
                    placeholder="PayPal Client Secret eingeben">
            </div>

            @if($existing)
                <div style="font-size:12px; color:#64748b; margin-bottom:16px;">
                    Zuletzt aktualisiert: {{ $existing['updated_at'] ?? '—' }}
                </div>
            @endif

            <button type="submit" class="btn btn-primary">Einstellungen speichern</button>
        </form>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card info-card">
            <h4>Einrichtung</h4>
            <p>So erhalten Sie Ihre PayPal API-Zugangsdaten:</p>
            <ul>
                <li>Melden Sie sich bei <a href="https://developer.paypal.com" target="_blank">developer.paypal.com</a> an</li>
                <li>Erstellen Sie eine neue App unter <strong>Apps & Credentials</strong></li>
                <li>Kopieren Sie <strong>Client ID</strong> und <strong>Secret</strong></li>
                <li>Verwenden Sie den <strong>Sandbox-Modus</strong> zum Testen</li>
            </ul>
        </div>

        <div class="card info-card">
            <h4>Unterstützte Funktionen</h4>
            <ul>
                <li>PayPal Checkout-Zahlungen</li>
                <li>Sandbox- und Live-Modus</li>
                <li>Lead-verknüpfte Zahlungen</li>
                <li>Transaktionsverlauf</li>
                <li>Automatische Erfassung</li>
            </ul>
        </div>

        <div class="card info-card">
            <h4>Währungen</h4>
            <p>EUR, USD, GBP und CHF werden unterstützt.</p>
        </div>
    </div>
</div>
@endsection
