@extends('layouts.shell')
@section('title', 'GmbH Analyse bearbeiten')
@section('page-title', '✏ GmbH Analyse bearbeiten')
@section('topbar-actions')
    <a href="{{ route('gmbh.show', $analysis) }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection
@push('styles')
<style>
    .gmbh-edit-wrap { max-width: 900px; }
    @media (max-width: 640px) {
        .gmbh-edit-wrap { max-width: 100%; }
    }
</style>
@endpush
@section('content')
<div class="gmbh-edit-wrap">
<form method="POST" action="{{ route('gmbh.update', $analysis) }}">
@csrf @method('PATCH')
<div class="card" style="margin-bottom:16px;">
    <div class="card-title">Grunddaten</div>
    <div class="form-grid">
        <div class="form-group col-span-2">
            <label class="form-label">Name der Analyse *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $analysis->name) }}" required>
        </div>
    </div>
</div>
<div class="card" style="margin-bottom:16px;">
    <div class="card-title">💰 Umsatz & Ergebnis</div>
    <div class="form-grid">
        @php $input = $analysis->gmbhInput; @endphp
        <div class="form-group">
            <label class="form-label">Umsatz aktuell (€) *</label>
            <input type="number" step="0.01" name="revenue_current" class="form-control" value="{{ old('revenue_current', $input->revenue_current) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Umsatz Vorjahr (€) *</label>
            <input type="number" step="0.01" name="revenue_prev" class="form-control" value="{{ old('revenue_prev', $input->revenue_prev) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">EBITDA (€)</label>
            <input type="number" step="0.01" name="ebitda" class="form-control" value="{{ old('ebitda', $input->ebitda) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Jahresüberschuss (€)</label>
            <input type="number" step="0.01" name="net_profit" class="form-control" value="{{ old('net_profit', $input->net_profit) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Eigenkapital (€) *</label>
            <input type="number" step="0.01" name="equity" class="form-control" value="{{ old('equity', $input->equity) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Gesamtverbindlichkeiten (€)</label>
            <input type="number" step="0.01" name="total_debt" class="form-control" value="{{ old('total_debt', $input->total_debt) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Umlaufvermögen (€)</label>
            <input type="number" step="0.01" name="current_assets" class="form-control" value="{{ old('current_assets', $input->current_assets) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Kurzfr. Verbindlichkeiten (€)</label>
            <input type="number" step="0.01" name="current_liabilities" class="form-control" value="{{ old('current_liabilities', $input->current_liabilities) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Cash (€)</label>
            <input type="number" step="0.01" name="cash" class="form-control" value="{{ old('cash', $input->cash) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Monatlicher Cashburn (€)</label>
            <input type="number" step="0.01" name="monthly_burn" class="form-control" value="{{ old('monthly_burn', $input->monthly_burn) }}">
        </div>
        <div class="form-group">
            <label class="form-label">CAC (€)</label>
            <input type="number" step="0.01" name="cac" class="form-control" value="{{ old('cac', $input->cac) }}">
        </div>
        <div class="form-group">
            <label class="form-label">LTV (€)</label>
            <input type="number" step="0.01" name="ltv" class="form-control" value="{{ old('ltv', $input->ltv) }}">
        </div>
    </div>
</div>
<div class="card" style="margin-bottom:16px;">
    <div class="card-title">🎯 Qualitative Scores</div>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Management-Score (1–10)</label>
            <input type="number" name="mgmt_score" class="form-control" min="1" max="10" value="{{ old('mgmt_score', $input->mgmt_score) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Markt-Score (1–10)</label>
            <input type="number" name="market_score" class="form-control" min="1" max="10" value="{{ old('market_score', $input->market_score) }}">
        </div>
    </div>
</div>
<div class="card" style="margin-bottom:16px;">
    <div class="card-title">⚖️ Gewichtung der KPIs (manuell)</div>
    <div style="font-size:11px; color:#64748b; margin-bottom:10px;">
        Prozentanteile je KPI fuer diese Analyse. Die Summe darf 100% nicht ueberschreiten.
    </div>
    @php
        $weights = $input->custom_weights ?? [];
    @endphp
    <div class="form-grid">
        @foreach([
            ['EBITDA_MARGE', 'EBITDA-Marge', 20],
            ['UMSATZ_WACHSTUM', 'Umsatzwachstum', 15],
            ['DEBT_EQUITY', 'Debt/Equity', 15],
            ['CURRENT_RATIO', 'Current Ratio', 10],
            ['RUNWAY', 'Runway', 10],
            ['LTV_CAC', 'LTV/CAC', 10],
            ['EK_QUOTE', 'Eigenkapitalquote', 10],
            ['MGMT_SCORE', 'Management', 10],
            ['MARKET_SCORE', 'Markt', 10],
        ] as [$code, $label, $defaultWeight])
        <div class="form-group">
            <label class="form-label">{{ $label }} (%)</label>
            <input
                type="number"
                name="weights[{{ $code }}]"
                class="form-control"
                min="0"
                max="100"
                step="1"
                value="{{ old('weights.' . $code, $weights[$code] ?? $defaultWeight) }}"
            >
        </div>
        @endforeach
    </div>
</div>
<button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px;">
    🔢 Neu berechnen & speichern
</button>
</form>
</div>
@endsection
