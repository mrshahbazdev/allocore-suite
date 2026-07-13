@extends('layouts.admin')
@section('title', 'Invoice Maker — Admin')
@section('page-title', '🧾 Invoice Maker Integration')

@section('topbar-actions')
    <a href="{{ route('admin.index') }}" class="btn btn-secondary btn-sm">Zurück</a>
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
        <div class="card-title">API-Konfiguration</div>

        <form method="POST" action="{{ route('admin.invoicemaker.save') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Invoice Maker URL *</label>
                <input type="url" name="base_url" class="form-control"
                    value="{{ old('base_url', $settings['base_url']) }}"
                    placeholder="https://invoice.allocore.de">
                <p class="form-hint">Die URL der Invoice Maker-Instanz (z.B. https://invoice.allocore.de)</p>
            </div>

            <div class="form-group">
                <label class="form-label">API-Key *</label>
                <input type="text" name="api_key" class="form-control"
                    value="{{ old('api_key', $settings['api_key']) }}"
                    placeholder="alc_xxxxxxxxxxxxxxxx">
                <p class="form-hint">
                    Den API-Key in Invoice Maker unter
                    <strong>Admin → Einstellungen → Allocore Integration</strong> generieren
                    und hier einfügen.
                </p>
            </div>

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom:12px;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="btn-row">
                <button type="submit" class="btn btn-primary">Einstellungen speichern</button>
            </div>
        </form>

        <div style="border-top: 1px solid rgba(220,38,38,0.1); margin-top: 24px; padding-top: 18px;">
            <div class="card-title">Verbindung testen</div>
            <form method="POST" action="{{ route('admin.invoicemaker.test') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">Verbindung testen</button>
            </form>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card info-card">
            <h4>Einrichtung</h4>
            <p>So verbinden Sie Allocore mit Invoice Maker:</p>
            <ul>
                <li>Öffnen Sie <a href="https://invoice.allocore.de" target="_blank">invoice.allocore.de</a></li>
                <li>Gehen Sie zu <strong>Admin → Einstellungen → Allocore</strong></li>
                <li>Klicken Sie auf <strong>API-Key generieren</strong></li>
                <li>Kopieren Sie den generierten Key und fügen Sie ihn hier ein</li>
                <li>Klicken Sie auf <strong>Einstellungen speichern</strong></li>
                <li>Testen Sie die Verbindung mit dem Button unten</li>
            </ul>
        </div>

        <div class="card info-card">
            <h4>Funktionsweise</h4>
            <p>Nach erfolgreicher Verbindung werden automatisch Rechnungen erstellt, wenn:</p>
            <ul>
                <li>Ein Benutzer eine PayPal-Zahlung abschließt</li>
                <li>Der Benutzer wird als Kunde synchronisiert</li>
                <li>Eine Rechnung mit allen Zahlungsdetails wird erstellt</li>
                <li>Die Rechnung ist sofort auf Invoice Maker verfügbar</li>
            </ul>
        </div>

        <div class="card info-card">
            <h4>Status</h4>
            @php
                $isConfigured = !empty($settings['api_key']) && !empty($settings['base_url']);
            @endphp
            @if($isConfigured)
                <p style="color: #34d399;">Konfiguriert — API-Key und URL sind gesetzt.</p>
            @else
                <p style="color: #fbbf24;">Nicht konfiguriert — Bitte API-Key und URL eingeben.</p>
            @endif
        </div>
    </div>
</div>
@endsection
