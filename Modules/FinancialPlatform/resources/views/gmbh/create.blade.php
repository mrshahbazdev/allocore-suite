@extends('layouts.shell')

@section('title', 'GmbH Analyse erstellen — Allocore')
@section('page-title', 'GmbH Analyse — Neue Analyse')

@section('topbar-actions')
    <a href="{{ route('gmbh.index') }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection

@push('styles')
<style>
    .gmbh-create-layout {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 20px;
        align-items: start;
    }
    .gmbh-create-left {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .gmbh-create-right {
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: sticky;
        top: 80px;
    }
    @media (max-width: 1024px) {
        .gmbh-create-layout {
            grid-template-columns: 1fr;
        }
        .gmbh-create-right {
            position: static;
        }
    }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('gmbh.store') }}">
@csrf

<div class="gmbh-create-layout">

    {{-- Left: Input fields --}}
    <div class="gmbh-create-left">

        {{-- Basic Info --}}
        <div class="card">
            <div class="card-title">📋 Grunddaten</div>
            <div class="form-grid">
                <div class="form-group col-span-2">
                    <label class="form-label">Name der Analyse *</label>
                    <input type="text" name="name" class="form-control" placeholder="z.B. GmbH Bewertung Q1 2024" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unternehmen *</label>
                    <select name="company_id" class="form-control" required>
                        <option value="">— Bitte wählen —</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($companies->isEmpty())
                        <div style="font-size:11px; color:#f59e0b; margin-top:4px;">
                            ⚠ <a href="{{ route('companies.create') }}" style="color:#f59e0b;">Erstellen Sie zuerst ein Unternehmen</a>
                        </div>
                    @endif
                </div>
                <div class="form-group">
                    <label class="form-label">Analysejahr</label>
                    <input type="text" name="year" class="form-control" placeholder="{{ date('Y') }}" value="{{ old('year', date('Y')) }}">
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="card">
            <div class="card-title">💰 Umsatz & Ergebnis</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Umsatz aktuelles Jahr (€) *</label>
                    <input type="number" step="0.01" name="revenue_current" class="form-control" placeholder="1.500.000" value="{{ old('revenue_current') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Umsatz Vorjahr (€) *</label>
                    <input type="number" step="0.01" name="revenue_prev" class="form-control" placeholder="1.200.000" value="{{ old('revenue_prev') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">EBITDA (€)</label>
                    <input type="number" step="0.01" name="ebitda" class="form-control" placeholder="300.000" value="{{ old('ebitda') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Jahresüberschuss / Net Profit (€)</label>
                    <input type="number" step="0.01" name="net_profit" class="form-control" placeholder="150.000" value="{{ old('net_profit') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Abschreibungen (€)</label>
                    <input type="number" step="0.01" name="depreciation" class="form-control" placeholder="50.000" value="{{ old('depreciation') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Zinsaufwand (€)</label>
                    <input type="number" step="0.01" name="interest" class="form-control" placeholder="20.000" value="{{ old('interest') }}">
                </div>
            </div>
        </div>

        {{-- Balance Sheet --}}
        <div class="card">
            <div class="card-title">📂 Bilanz</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Eigenkapital (€) *</label>
                    <input type="number" step="0.01" name="equity" class="form-control" placeholder="500.000" value="{{ old('equity') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Gesamtverbindlichkeiten (€)</label>
                    <input type="number" step="0.01" name="total_debt" class="form-control" placeholder="800.000" value="{{ old('total_debt') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Bilanzsumme (€)</label>
                    <input type="number" step="0.01" name="total_assets" class="form-control" placeholder="1.300.000" value="{{ old('total_assets') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Umlaufvermögen (€)</label>
                    <input type="number" step="0.01" name="current_assets" class="form-control" placeholder="600.000" value="{{ old('current_assets') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Kurzfr. Verbindlichkeiten (€)</label>
                    <input type="number" step="0.01" name="current_liabilities" class="form-control" placeholder="300.000" value="{{ old('current_liabilities') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Kassenbestand / Cash (€)</label>
                    <input type="number" step="0.01" name="cash" class="form-control" placeholder="180.000" value="{{ old('cash') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Monatlicher Cashburn (€)</label>
                    <input type="number" step="0.01" name="monthly_burn" class="form-control" placeholder="30.000" value="{{ old('monthly_burn') }}">
                    <div style="font-size:11px; color:#475569; margin-top:3px;">Für Runway-Berechnung</div>
                </div>
            </div>
        </div>

        {{-- Customer Metrics --}}
        <div class="card">
            <div class="card-title">👥 Kundenmetriken (Optional)</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">CAC — Customer Acquisition Cost (€)</label>
                    <input type="number" step="0.01" name="cac" class="form-control" placeholder="500" value="{{ old('cac') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">LTV — Lifetime Value (€)</label>
                    <input type="number" step="0.01" name="ltv" class="form-control" placeholder="2.000" value="{{ old('ltv') }}">
                </div>
            </div>
        </div>

    </div>

    {{-- Right: Qualitative Scores --}}
    <div class="gmbh-create-right">

        <div class="card">
            <div class="card-title">🎯 Qualitative Bewertung</div>

            <div class="form-group">
                <label class="form-label">Management-Qualität (1–10) *</label>
                <input type="range" name="mgmt_score" id="mgmt_score" min="1" max="10" step="1" value="{{ old('mgmt_score', 7) }}"
                    style="width:100%; accent-color:#6366f1;" oninput="document.getElementById('mgmt_val').textContent=this.value">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:#475569; margin-top:4px;">
                    <span>1 — Kritisch</span>
                    <span style="font-size:16px; font-weight:700; color:#818cf8;" id="mgmt_val">{{ old('mgmt_score', 7) }}</span>
                    <span>10 — Exzellent</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Markt & Wettbewerb (1–10) *</label>
                <input type="range" name="market_score" id="market_score" min="1" max="10" step="1" value="{{ old('market_score', 7) }}"
                    style="width:100%; accent-color:#6366f1;" oninput="document.getElementById('market_val').textContent=this.value">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:#475569; margin-top:4px;">
                    <span>1 — Schwach</span>
                    <span style="font-size:16px; font-weight:700; color:#818cf8;" id="market_val">{{ old('market_score', 7) }}</span>
                    <span>10 — Dominant</span>
                </div>
            </div>
        </div>

        {{-- KPI Weights Overview --}}
        <div class="card">
            <div class="card-title">⚖️ Gewichtung der KPIs (manuell)</div>
            <div style="font-size:11px; color:#64748b; margin-bottom:10px;">
                Prozentanteile je KPI (0-100). Die Summe darf 100% nicht ueberschreiten.
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                @foreach([
                    ['EBITDA_MARGE', 'EBITDA-Marge', 20, '#10b981'],
                    ['UMSATZ_WACHSTUM', 'Umsatzwachstum', 15, '#818cf8'],
                    ['DEBT_EQUITY', 'Debt/Equity', 15, '#818cf8'],
                    ['CURRENT_RATIO', 'Current Ratio', 10, '#6366f1'],
                    ['RUNWAY', 'Runway', 10, '#6366f1'],
                    ['LTV_CAC', 'LTV/CAC', 10, '#6366f1'],
                    ['EK_QUOTE', 'Eigenkapitalquote', 10, '#6366f1'],
                    ['MGMT_SCORE', 'Management', 10, '#6366f1'],
                    ['MARKET_SCORE', 'Markt', 10, '#6366f1'],
                ] as [$code, $name, $defaultWeight, $color])
                <div style="display:flex; align-items:center; gap:8px; font-size:12px;">
                    <div style="flex:1; color:#94a3b8;">{{ $name }}</div>
                    <input
                        type="number"
                        min="0"
                        max="100"
                        step="1"
                        name="weights[{{ $code }}]"
                        value="{{ old('weights.' . $code, $defaultWeight) }}"
                        class="form-control"
                        style="width:72px; padding:6px 8px; color:{{ $color }}; font-weight:600;"
                    >
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; font-size:14px;">
            🔢 Analyse berechnen & speichern
        </button>

    </div>
</div>

</form>

@endsection
