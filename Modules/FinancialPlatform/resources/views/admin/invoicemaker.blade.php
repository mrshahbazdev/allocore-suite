@extends('layouts.admin')
@section('title', 'Invoice Maker — Admin')
@section('page-title', '🧾 Invoice Maker Integration')

@section('topbar-actions')
    <a href="{{ route('admin.index') }}" class="btn btn-secondary btn-sm">{{ __('Zurück') }}</a>
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
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 12px; font-weight: 500; color: #94a3b8; margin-bottom: 6px; }
    .form-hint { font-size: 11px; color: #475569; margin-top: 4px; }
    .btn-row { display: flex; gap: 10px; margin-top: 20px; }
    @media (max-width: 900px) { .settings-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="settings-grid">
    <div class="card">
        <div class="card-title">{{ __('API-Konfiguration') }}</div>

        <form method="POST" action="{{ route('admin.invoicemaker.save') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">{{ __('Invoice Maker URL *') }}</label>
                <input type="url" name="base_url" class="form-control"
                    value="{{ old('base_url', $settings['base_url']) }}"
                    placeholder="{{ __('https://invoice.allocore.de') }}">
                <p class="form-hint">{{ __('Die URL der Invoice Maker-Instanz (z.B. https://invoice.allocore.de)') }}</p>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('API-Key *') }}</label>
                <input type="text" name="api_key" class="form-control"
                    value="{{ old('api_key', $settings['api_key']) }}"
                    placeholder="{{ __('alc_xxxxxxxxxxxxxxxx') }}">
                <p class="form-hint">{{ __('Den API-Key in Invoice Maker unter') }}<strong>{{ __('Admin → Einstellungen → Allocore Integration') }}</strong>{{ __('generieren
                    und hier einfügen.') }}</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom:12px;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="btn-row">
                <button type="submit" class="btn btn-primary">{{ __('Einstellungen speichern') }}</button>
            </div>
        </form>

        <div style="border-top: 1px solid rgba(220,38,38,0.1); margin-top: 24px; padding-top: 18px;">
            <div class="card-title">{{ __('Verbindung testen') }}</div>
            <form method="POST" action="{{ route('admin.invoicemaker.test') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">{{ __('Verbindung testen') }}</button>
            </form>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card info-card">
            <h4>{{ __('Einrichtung') }}</h4>
            <p>{{ __('So verbinden Sie Allocore mit Invoice Maker:') }}</p>
            <ul>
                <li>{{ __('Öffnen Sie') }}<a href="https://invoice.allocore.de" target="_blank">{{ __('invoice.allocore.de') }}</a></li>
                <li>{{ __('Gehen Sie zu') }}<strong>{{ __('Admin → Einstellungen → Allocore') }}</strong></li>
                <li>{{ __('Klicken Sie auf') }}<strong>{{ __('API-Key generieren') }}</strong></li>
                <li>{{ __('Kopieren Sie den generierten Key und fügen Sie ihn hier ein') }}</li>
                <li>{{ __('Klicken Sie auf') }}<strong>{{ __('Einstellungen speichern') }}</strong></li>
                <li>{{ __('Testen Sie die Verbindung mit dem Button unten') }}</li>
            </ul>
        </div>

        <div class="card info-card">
            <h4>{{ __('Funktionsweise') }}</h4>
            <p>{{ __('Nach erfolgreicher Verbindung werden automatisch Rechnungen erstellt, wenn:') }}</p>
            <ul>
                <li>{{ __('Ein Benutzer eine PayPal-Zahlung abschließt') }}</li>
                <li>{{ __('Der Benutzer wird als Kunde synchronisiert') }}</li>
                <li>{{ __('Eine Rechnung mit allen Zahlungsdetails wird erstellt') }}</li>
                <li>{{ __('Die Rechnung ist sofort auf Invoice Maker verfügbar') }}</li>
            </ul>
        </div>

        <div class="card info-card">
            <h4>{{ __('Status') }}</h4>
            @php
                $isConfigured = !empty($settings['api_key']) && !empty($settings['base_url']);
            @endphp
            @if($isConfigured)
                <p style="color: #34d399;">{{ __('Konfiguriert — API-Key und URL sind gesetzt.') }}</p>
            @else
                <p style="color: #fbbf24;">{{ __('Nicht konfiguriert — Bitte API-Key und URL eingeben.') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
