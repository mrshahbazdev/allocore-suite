@extends('layouts.shell')

@section('title', 'PayPal — Allocore')
@section('page-title', 'PayPal-Zahlungen')

@section('topbar-actions')
    <a href="{{ route('paypal.settings') }}" class="btn btn-secondary btn-sm">Einstellungen</a>
@endsection

@push('styles')
<style>
    .paypal-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
    .paypal-stat { text-align: center; padding: 20px 10px; }
    .paypal-stat .num { font-size: 28px; font-weight: 700; }
    .paypal-stat .lbl { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-top: 4px; }
    .new-payment-card { margin-bottom: 24px; }
    .payment-form-row { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
    .payment-form-row .form-group { margin-bottom: 0; }
    @media (max-width: 640px) { .paypal-stats { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

{{-- Stats --}}
<div class="paypal-stats">
    <div class="card paypal-stat">
        <div class="num" style="color:#10b981;">{{ number_format($stats['total_amount'], 2, ',', '.') }} EUR</div>
        <div class="lbl">Gesamtumsatz</div>
    </div>
    <div class="card paypal-stat">
        <div class="num" style="color:#818cf8;">{{ $stats['total_count'] }}</div>
        <div class="lbl">Abgeschlossen</div>
    </div>
    <div class="card paypal-stat">
        <div class="num" style="color:#fbbf24;">{{ $stats['pending'] }}</div>
        <div class="lbl">Ausstehend</div>
    </div>
</div>

{{-- New Payment --}}
<div class="card new-payment-card">
    <div class="card-title">Neue Zahlung erstellen</div>
    <form method="POST" action="{{ route('paypal.create-payment') }}" class="payment-form-row">
        @csrf
        <div class="form-group">
            <label class="form-label">Betrag *</label>
            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00" style="width:140px;">
        </div>
        <div class="form-group">
            <label class="form-label">Währung</label>
            <select name="currency" class="form-control" style="width:100px;">
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
                <option value="GBP">GBP</option>
                <option value="CHF">CHF</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Beschreibung</label>
            <input type="text" name="description" class="form-control" placeholder="z.B. Beratungsgebühr" style="min-width:200px;">
        </div>
        <div class="form-group">
            <label class="form-label">Lead (optional)</label>
            <select name="lead_id" class="form-control" style="min-width:160px;">
                <option value="">— Kein Lead —</option>
                @php $userLeads = \App\Models\Lead::where('user_id', auth()->id())->orderBy('name')->get(); @endphp
                @foreach($userLeads as $l)
                    <option value="{{ $l->id }}">{{ $l->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Mit PayPal bezahlen</button>
        </div>
    </form>
</div>

{{-- Transactions --}}
<div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
        <div class="card-title" style="margin-bottom:0">Transaktionen</div>
        <form method="GET" action="{{ route('paypal.index') }}" style="display:flex; gap:8px;">
            <select name="status" class="form-control" style="width:auto; font-size:12px; padding:5px 10px;" onchange="this.form.submit()">
                <option value="">Alle</option>
                @foreach(['pending'=>'Ausstehend','completed'=>'Abgeschlossen','failed'=>'Fehlgeschlagen'] as $k=>$v)
                    <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($transactions->isEmpty())
        <div style="text-align:center; padding:40px; color:#475569;">
            <div style="font-size:40px; margin-bottom:12px;">💳</div>
            <div style="font-size:14px; margin-bottom:8px;">Noch keine PayPal-Transaktionen</div>
            <div style="font-size:12px; color:#334155;">Erstellen Sie eine Zahlung oder konfigurieren Sie PayPal</div>
        </div>
    @else
        <div class="dashboard-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bestell-ID</th>
                    <th>Betrag</th>
                    <th>Zahler</th>
                    <th>Lead</th>
                    <th>Status</th>
                    <th>Beschreibung</th>
                    <th>Datum</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $txn)
                <tr>
                    <td style="font-family:monospace; font-size:11px; color:#818cf8;">{{ Str::limit($txn->paypal_order_id, 20) }}</td>
                    <td style="font-weight:600; color:#e2e8f0;">{{ number_format($txn->amount, 2, ',', '.') }} {{ $txn->currency }}</td>
                    <td>{{ $txn->payer_name ?? $txn->payer_email ?? '—' }}</td>
                    <td>
                        @if($txn->lead)
                            <a href="{{ route('leads.show', $txn->lead) }}" style="color:#818cf8; text-decoration:none;">{{ $txn->lead->name }}</a>
                        @else — @endif
                    </td>
                    <td>
                        <span class="badge {{ $txn->status === 'completed' ? 'badge-green' : ($txn->status === 'pending' ? 'badge-yellow' : 'badge-red') }}">
                            {{ $txn->status === 'completed' ? 'Abgeschlossen' : ($txn->status === 'pending' ? 'Ausstehend' : 'Fehlgeschlagen') }}
                        </span>
                    </td>
                    <td style="font-size:12px;">{{ $txn->description ?? '—' }}</td>
                    <td style="font-size:12px;">{{ $txn->created_at->format('d.m.Y H:i') }}</td>
                    <td><a href="{{ route('paypal.show', $txn) }}" class="btn btn-secondary btn-sm">Details</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <div style="margin-top:16px;">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
