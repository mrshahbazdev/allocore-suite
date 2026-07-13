@extends('layouts.shell')

@section('title', 'Leads — Allocore')
@section('page-title', 'Lead-Verwaltung')

@section('topbar-actions')
    <a href="{{ route('leads.export') }}" class="btn btn-secondary btn-sm">CSV Export</a>
    <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">+ Neuer Lead</a>
@endsection

@push('styles')
<style>
    .leads-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 24px; }
    .lead-stat { text-align: center; padding: 16px 10px; }
    .lead-stat .num { font-size: 28px; font-weight: 700; }
    .lead-stat .lbl { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-top: 4px; }
    .filters-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
    .filters-bar .form-control { width: auto; min-width: 160px; padding: 7px 12px; font-size: 12px; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .priority-badge { font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 600; text-transform: uppercase; }
    .transfer-section { margin-top: 24px; }
    .checkbox-cell { width: 36px; text-align: center; }
    .checkbox-cell input { accent-color: #6366f1; width: 16px; height: 16px; cursor: pointer; }
    @media (max-width: 900px) { .leads-stats { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px) { .leads-stats { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@section('content')

{{-- Stats --}}
<div class="leads-stats">
    <div class="card lead-stat">
        <div class="num" style="color:#818cf8;">{{ $stats['total'] }}</div>
        <div class="lbl">Gesamt</div>
    </div>
    <div class="card lead-stat">
        <div class="num" style="color:#38bdf8;">{{ $stats['new'] }}</div>
        <div class="lbl">Neu</div>
    </div>
    <div class="card lead-stat">
        <div class="num" style="color:#fbbf24;">{{ $stats['contacted'] }}</div>
        <div class="lbl">Kontaktiert</div>
    </div>
    <div class="card lead-stat">
        <div class="num" style="color:#10b981;">{{ $stats['qualified'] }}</div>
        <div class="lbl">Qualifiziert</div>
    </div>
    <div class="card lead-stat">
        <div class="num" style="color:#c084fc;">{{ $stats['transferred'] }}</div>
        <div class="lbl">An LeadOS</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('leads.index') }}" class="filters-bar">
    <input type="text" name="search" class="form-control" placeholder="Name, E-Mail, Firma..." value="{{ request('search') }}">
    <select name="status" class="form-control">
        <option value="">Alle Status</option>
        @foreach(['new'=>'Neu','contacted'=>'Kontaktiert','qualified'=>'Qualifiziert','proposal'=>'Angebot','won'=>'Gewonnen','lost'=>'Verloren'] as $k=>$v)
            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
        @endforeach
    </select>
    <select name="priority" class="form-control">
        <option value="">Alle Prioritäten</option>
        @foreach(['low'=>'Niedrig','medium'=>'Mittel','high'=>'Hoch','critical'=>'Kritisch'] as $k=>$v)
            <option value="{{ $k }}" {{ request('priority') === $k ? 'selected' : '' }}>{{ $v }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filtern</button>
    @if(request()->hasAny(['search','status','priority']))
        <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-sm" style="color:#f87171;">Zurücksetzen</a>
    @endif
</form>

{{-- Transfer Form wraps the table --}}
<form method="POST" action="{{ route('leads.transfer') }}" id="transferForm">
    @csrf

    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <div class="card-title" style="margin-bottom:0">Leads ({{ $leads->total() }})</div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <input type="url" name="leados_api_url" class="form-control" placeholder="LeadOS URL (z.B. https://lead.os)"
                    value="{{ old('leados_api_url') }}" style="min-width:200px; font-size:12px; padding:6px 10px;">
                <input type="text" name="leados_token" class="form-control" placeholder="LeadOS API-Token"
                    value="{{ old('leados_token') }}" style="min-width:160px; font-size:12px; padding:6px 10px;">
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Ausgewählte Leads an LeadOS übertragen?')">
                    An LeadOS senden
                </button>
            </div>
        </div>

        @if($leads->isEmpty())
            <div style="text-align:center; padding:40px; color:#475569;">
                <div style="font-size:40px; margin-bottom:12px;">👤</div>
                <div style="font-size:14px; margin-bottom:8px;">Noch keine Leads vorhanden</div>
                <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">Ersten Lead erstellen</a>
            </div>
        @else
            <div class="dashboard-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="checkbox-cell"><input type="checkbox" id="selectAll"></th>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Unternehmen</th>
                        <th>Status</th>
                        <th>Priorität</th>
                        <th>Quelle</th>
                        <th>LeadOS</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                    <tr>
                        <td class="checkbox-cell">
                            @unless($lead->transferred_to_leados)
                                <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" class="lead-check">
                            @endunless
                        </td>
                        <td style="font-weight:500; color:#e2e8f0;">{{ $lead->name }}</td>
                        <td>{{ $lead->email ?? '—' }}</td>
                        <td>{{ $lead->company_name ?? $lead->company?->name ?? '—' }}</td>
                        <td>
                            @php
                                $statusColors = ['new'=>'#38bdf8','contacted'=>'#fbbf24','qualified'=>'#10b981','proposal'=>'#818cf8','won'=>'#34d399','lost'=>'#f87171'];
                                $statusLabels = ['new'=>'Neu','contacted'=>'Kontaktiert','qualified'=>'Qualifiziert','proposal'=>'Angebot','won'=>'Gewonnen','lost'=>'Verloren'];
                            @endphp
                            <span style="color:{{ $statusColors[$lead->status] ?? '#94a3b8' }}; font-size:12px; font-weight:500;">
                                <span class="status-dot" style="background:{{ $statusColors[$lead->status] ?? '#94a3b8' }};"></span>
                                {{ $statusLabels[$lead->status] ?? $lead->status }}
                            </span>
                        </td>
                        <td>
                            @php
                                $prioColors = ['low'=>'badge-gray','medium'=>'badge-yellow','high'=>'badge-red','critical'=>'badge-red'];
                                $prioLabels = ['low'=>'Niedrig','medium'=>'Mittel','high'=>'Hoch','critical'=>'Kritisch'];
                            @endphp
                            <span class="badge {{ $prioColors[$lead->priority] ?? 'badge-gray' }}">{{ $prioLabels[$lead->priority] ?? $lead->priority }}</span>
                        </td>
                        <td style="font-size:12px;">{{ $lead->source }}</td>
                        <td>
                            @if($lead->transferred_to_leados)
                                <span class="badge badge-green">Übertragen</span>
                            @else
                                <span class="badge badge-gray">Ausstehend</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('leads.show', $lead) }}" class="btn btn-secondary btn-sm">Ansehen</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div style="margin-top:16px;">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.lead-check').forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
