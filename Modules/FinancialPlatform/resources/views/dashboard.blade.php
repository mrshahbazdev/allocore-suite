@extends('layouts.shell')

@section('title', 'Dashboard — Allocore')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('gmbh.create') }}" class="btn btn-primary btn-sm">+ Neue Analyse</a>
@endsection

@push('styles')
<style>
    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .dashboard-main-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
    }
    .dashboard-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .dashboard-side-stack { display: flex; flex-direction: column; gap: 16px; }
    @media (max-width: 1200px) {
        .dashboard-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 900px) {
        .dashboard-main-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .dashboard-stats-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- Stats Grid --}}
<div class="dashboard-stats-grid">

    <div class="card" style="padding:20px;">
        <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">Unternehmen</div>
        <div style="font-size:36px; font-weight:700; color:#818cf8;">{{ $stats['companies'] }}</div>
        <a href="{{ route('companies.create') }}" style="font-size:12px; color:#6366f1; text-decoration:none; margin-top:6px; display:block;">+ Hinzufügen →</a>
    </div>

    <div class="card" style="padding:20px; border-color:rgba(16,185,129,0.2);">
        <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">📊 GmbH Analysen</div>
        <div style="font-size:36px; font-weight:700; color:#10b981;">{{ $stats['gmbh'] }}</div>
        <a href="{{ route('gmbh.create') }}" style="font-size:12px; color:#10b981; text-decoration:none; margin-top:6px; display:block;">+ Neue Analyse →</a>
    </div>

    <div class="card" style="padding:20px; border-color:rgba(245,158,11,0.2);">
        <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">📈 Jahresabschlüsse</div>
        <div style="font-size:36px; font-weight:700; color:#f59e0b;">{{ $stats['jahresabschluss'] }}</div>
        <a href="{{ route('jahresabschluss.create') }}" style="font-size:12px; color:#f59e0b; text-decoration:none; margin-top:6px; display:block;">+ Neu erstellen →</a>
    </div>

    <div class="card" style="padding:20px; border-color:rgba(168,85,247,0.2);">
        <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">🏘 Immobilien</div>
        <div style="font-size:36px; font-weight:700; color:#a855f7;">{{ $stats['immobilien'] }}</div>
        <a href="{{ route('immobilien.create') }}" style="font-size:12px; color:#a855f7; text-decoration:none; margin-top:6px; display:block;">+ Analysieren →</a>
    </div>

    <div class="card" style="padding:20px; border-color:rgba(56,189,248,0.2);">
        <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">👤 Leads</div>
        <div style="font-size:36px; font-weight:700; color:#38bdf8;">{{ $stats['leads'] }}</div>
        <a href="{{ route('leads.create') }}" style="font-size:12px; color:#38bdf8; text-decoration:none; margin-top:6px; display:block;">+ Neuer Lead →</a>
    </div>

    <div class="card" style="padding:20px; border-color:rgba(16,185,129,0.2);">
        <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">💳 PayPal Umsatz</div>
        <div style="font-size:36px; font-weight:700; color:#10b981;">{{ number_format($stats['paypal_revenue'], 0, ',', '.') }} €</div>
        <a href="{{ route('paypal.index') }}" style="font-size:12px; color:#10b981; text-decoration:none; margin-top:6px; display:block;">Transaktionen →</a>
    </div>

</div>

<div class="dashboard-main-grid">

    {{-- Recent Analyses --}}
    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
            <div class="card-title" style="margin-bottom:0">Letzte Analysen</div>
            <a href="{{ route('analyses.index') }}" class="btn btn-secondary btn-sm">Alle anzeigen</a>
        </div>

        @if($recentAnalyses->isEmpty())
            <div style="text-align:center; padding:40px; color:#475569;">
                <div style="font-size:40px; margin-bottom:12px;">📊</div>
                <div style="font-size:14px; margin-bottom:8px;">Noch keine Analysen vorhanden</div>
                <div style="font-size:12px; color:#334155; margin-bottom:16px;">Starten Sie mit einer GmbH oder Immobilien-Analyse</div>
                <a href="{{ route('gmbh.create') }}" class="btn btn-primary btn-sm">Erste Analyse erstellen</a>
            </div>
        @else
            <div class="dashboard-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Unternehmen</th>
                        <th>Typ</th>
                        <th>Score</th>
                        <th>Datum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAnalyses as $a)
                    <tr>
                        <td style="font-weight:500; color:#e2e8f0;">{{ $a->name }}</td>
                        <td>{{ $a->company->name ?? '—' }}</td>
                        <td>
                            @php $typeColors = ['gmbh'=>'#818cf8','jahresabschluss'=>'#fbbf24','immobilien'=>'#c084fc']; @endphp
                            <span style="font-size:11px; color:{{ $typeColors[$a->type] ?? '#94a3b8' }}; font-weight:500;">
                                {{ $a->typeLabel() }}
                            </span>
                        </td>
                        <td>
                            @if($a->total_score !== null)
                                <span class="score-{{ $a->scoreColor() }}" style="font-weight:700; font-size:16px;">
                                    {{ number_format($a->total_score, 1) }}
                                </span>
                                <span style="font-size:11px; color:#475569;">/100</span>
                            @else
                                <span style="color:#475569;">—</span>
                            @endif
                        </td>
                        <td style="font-size:12px; color:#475569;">{{ $a->created_at->format('d.m.Y') }}</td>
                        <td>
                            <a href="{{ route($a->type . '.show', $a) }}" class="btn btn-secondary btn-sm">→</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    {{-- Quick Start + Companies --}}
    <div class="dashboard-side-stack">

        <div class="card">
            <div class="card-title">🚀 Quick Start</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('gmbh.create') }}" class="btn btn-primary" style="justify-content:center;">
                    📊 GmbH Analyse starten
                </a>
                <a href="{{ route('jahresabschluss.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    📈 Jahresabschluss anlegen
                </a>
                <a href="{{ route('immobilien.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    🏘 Immobilie analysieren
                </a>
                <a href="{{ route('companies.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    🏢 Unternehmen anlegen
                </a>
            </div>
        </div>

        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="card-title" style="margin-bottom:0;">🏢 Unternehmen</div>
                <a href="{{ route('companies.index') }}" style="font-size:12px; color:#6366f1; text-decoration:none;">Alle →</a>
            </div>
            @forelse($companies as $company)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                <div>
                    <div style="font-size:13px; font-weight:500; color:#e2e8f0;">{{ $company->name }}</div>
                    <div style="font-size:11px; color:#475569;">{{ $company->analyses_count }} Analysen</div>
                </div>
                <a href="{{ route('companies.show', $company) }}" style="font-size:12px; color:#6366f1; text-decoration:none;">→</a>
            </div>
            @empty
            <div style="font-size:13px; color:#475569; text-align:center; padding:16px 0;">
                Noch keine Unternehmen
            </div>
            @endforelse
        </div>

    </div>
</div>

@endsection
