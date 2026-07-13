@extends('layouts.shell')

@section('title', $lead->name . ' — Lead — Allocore')
@section('page-title', 'Lead: ' . $lead->name)

@section('topbar-actions')
    <a href="{{ route('leads.edit', $lead) }}" class="btn btn-secondary btn-sm">Bearbeiten</a>
    <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-sm">Zurück</a>
@endsection

@push('styles')
<style>
    .lead-detail-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
    .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13px; }
    .detail-label { color: #64748b; font-weight: 500; }
    .detail-value { color: #e2e8f0; text-align: right; }
    @media (max-width: 900px) { .lead-detail-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="lead-detail-grid">
    {{-- Main info --}}
    <div class="card">
        <div class="card-title">Lead-Details</div>

        <div class="detail-row">
            <span class="detail-label">Name</span>
            <span class="detail-value">{{ $lead->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">E-Mail</span>
            <span class="detail-value">{{ $lead->email ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Telefon</span>
            <span class="detail-value">{{ $lead->phone ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Position</span>
            <span class="detail-value">{{ $lead->position ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Unternehmen</span>
            <span class="detail-value">{{ $lead->company_name ?? $lead->company?->name ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Branche</span>
            <span class="detail-value">{{ $lead->industry ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">LinkedIn</span>
            <span class="detail-value">
                @if($lead->linkedin)
                    <a href="{{ $lead->linkedin }}" target="_blank" style="color:#818cf8;">Profil öffnen</a>
                @else — @endif
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Website</span>
            <span class="detail-value">
                @if($lead->website)
                    <a href="{{ $lead->website }}" target="_blank" style="color:#818cf8;">{{ parse_url($lead->website, PHP_URL_HOST) }}</a>
                @else — @endif
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Budget</span>
            <span class="detail-value">{{ $lead->budget ? number_format($lead->budget, 2, ',', '.') . ' EUR' : '—' }}</span>
        </div>

        @if($lead->notes)
        <div style="margin-top:16px;">
            <div class="detail-label" style="margin-bottom:6px;">Notizen</div>
            <div style="background:rgba(255,255,255,0.04); padding:12px; border-radius:8px; font-size:13px; color:#cbd5e1; white-space:pre-wrap;">{{ $lead->notes }}</div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div style="display:flex; flex-direction:column; gap:16px;">
        {{-- Status card --}}
        <div class="card">
            <div class="card-title">Status</div>
            @php
                $statusColors = ['new'=>'#38bdf8','contacted'=>'#fbbf24','qualified'=>'#10b981','proposal'=>'#818cf8','won'=>'#34d399','lost'=>'#f87171'];
                $statusLabels = ['new'=>'Neu','contacted'=>'Kontaktiert','qualified'=>'Qualifiziert','proposal'=>'Angebot','won'=>'Gewonnen','lost'=>'Verloren'];
                $prioLabels = ['low'=>'Niedrig','medium'=>'Mittel','high'=>'Hoch','critical'=>'Kritisch'];
            @endphp
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span style="color:{{ $statusColors[$lead->status] ?? '#94a3b8' }}; font-weight:500; font-size:13px;">
                    {{ $statusLabels[$lead->status] ?? $lead->status }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Priorität</span>
                <span style="font-weight:500; font-size:13px;">{{ $prioLabels[$lead->priority] ?? $lead->priority }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Quelle</span>
                <span class="detail-value">{{ $lead->source }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Erstellt</span>
                <span class="detail-value">{{ $lead->created_at->format('d.m.Y H:i') }}</span>
            </div>
        </div>

        {{-- LeadOS transfer --}}
        <div class="card">
            <div class="card-title">LeadOS Integration</div>
            @if($lead->transferred_to_leados)
                <div style="text-align:center; padding:12px;">
                    <span class="badge badge-green" style="font-size:13px; padding:6px 14px;">An LeadOS übertragen</span>
                    <div style="font-size:11px; color:#64748b; margin-top:8px;">{{ $lead->transferred_at?->format('d.m.Y H:i') }}</div>
                </div>
            @else
                <form method="POST" action="{{ route('leads.transfer') }}">
                    @csrf
                    <input type="hidden" name="lead_ids[]" value="{{ $lead->id }}">
                    <div class="form-group">
                        <label class="form-label">LeadOS API-URL</label>
                        <input type="url" name="leados_api_url" class="form-control" placeholder="https://lead.os" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">API-Token</label>
                        <input type="text" name="leados_token" class="form-control" placeholder="Bearer Token" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm" style="width:100%; justify-content:center;">
                        An LeadOS übertragen
                    </button>
                </form>
            @endif
        </div>

        {{-- PayPal Transactions --}}
        @if($lead->paypalTransactions->count())
        <div class="card">
            <div class="card-title">PayPal-Transaktionen</div>
            @foreach($lead->paypalTransactions as $txn)
                <div class="detail-row">
                    <span class="detail-label">{{ number_format($txn->amount, 2, ',', '.') }} {{ $txn->currency }}</span>
                    <span class="badge {{ $txn->status === 'completed' ? 'badge-green' : ($txn->status === 'pending' ? 'badge-yellow' : 'badge-red') }}">
                        {{ ucfirst($txn->status) }}
                    </span>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Actions --}}
        <div class="card">
            <div class="card-title">Aktionen</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('leads.edit', $lead) }}" class="btn btn-secondary btn-sm" style="justify-content:center;">Bearbeiten</a>
                <form method="POST" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('Lead wirklich löschen?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" style="width:100%; justify-content:center;">Löschen</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
