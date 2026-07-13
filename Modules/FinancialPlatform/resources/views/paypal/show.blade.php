@extends('layouts.shell')

@section('title', 'Transaktion — Allocore')
@section('page-title', 'Transaktionsdetails')

@section('topbar-actions')
    <a href="{{ route('paypal.index') }}" class="btn btn-secondary btn-sm">Zurück</a>
@endsection

@push('styles')
<style>
    .txn-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
    .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13px; }
    .detail-label { color: #64748b; font-weight: 500; }
    .detail-value { color: #e2e8f0; text-align: right; }
    .json-block { background: rgba(0,0,0,0.3); padding: 14px; border-radius: 8px; font-family: monospace; font-size: 11px; color: #94a3b8; overflow-x: auto; max-height: 400px; white-space: pre-wrap; word-break: break-all; }
    @media (max-width: 900px) { .txn-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="txn-grid">
    <div class="card">
        <div class="card-title">Zahlungsdetails</div>

        <div class="detail-row">
            <span class="detail-label">PayPal Bestell-ID</span>
            <span class="detail-value" style="font-family:monospace;">{{ $transaction->paypal_order_id }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Betrag</span>
            <span class="detail-value" style="font-weight:700; font-size:18px; color:#10b981;">
                {{ number_format($transaction->amount, 2, ',', '.') }} {{ $transaction->currency }}
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="badge {{ $transaction->status === 'completed' ? 'badge-green' : ($transaction->status === 'pending' ? 'badge-yellow' : 'badge-red') }}">
                {{ $transaction->status === 'completed' ? 'Abgeschlossen' : ($transaction->status === 'pending' ? 'Ausstehend' : 'Fehlgeschlagen') }}
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Beschreibung</span>
            <span class="detail-value">{{ $transaction->description ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Zahler Name</span>
            <span class="detail-value">{{ $transaction->payer_name ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Zahler E-Mail</span>
            <span class="detail-value">{{ $transaction->payer_email ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Lead</span>
            <span class="detail-value">
                @if($transaction->lead)
                    <a href="{{ route('leads.show', $transaction->lead) }}" style="color:#818cf8;">{{ $transaction->lead->name }}</a>
                @else — @endif
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Erstellt</span>
            <span class="detail-value">{{ $transaction->created_at->format('d.m.Y H:i:s') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Aktualisiert</span>
            <span class="detail-value">{{ $transaction->updated_at->format('d.m.Y H:i:s') }}</span>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-title">PayPal API-Antwort</div>
            <div class="json-block">{{ json_encode($transaction->paypal_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
        </div>
    </div>
</div>
@endsection
