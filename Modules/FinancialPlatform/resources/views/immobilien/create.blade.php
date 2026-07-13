@extends('layouts.shell')
@section('title', 'Immobilie analysieren — Allocore')
@section('page-title', '🏘 Neue Immobilienanalyse')
@section('topbar-actions')
    <a href="{{ route('immobilien.index') }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection
@push('styles')
<style>
    .immo-create-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }
    .immo-create-left {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .immo-create-right {
        position: sticky;
        top: 80px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    @media (max-width: 1024px) {
        .immo-create-layout {
            grid-template-columns: 1fr;
        }
        .immo-create-right {
            position: static;
        }
    }
</style>
@endpush
@section('content')
<form method="POST" action="{{ route('immobilien.store') }}">
@csrf
<div class="immo-create-layout">

<div class="immo-create-left">

  <div class="card">
    <div class="card-title">📋 Grunddaten</div>
    <div class="form-grid">
      <div class="form-group col-span-2">
        <label class="form-label">Name der Analyse *</label>
        <input type="text" name="name" class="form-control" placeholder="Mehrfamilienhaus Berlin 2024" value="{{ old('name') }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Unternehmen *</label>
        <select name="company_id" class="form-control" required>
          <option value="">— Bitte wählen —</option>
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ old('company_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Objekttyp</label>
        <select name="property_type" class="form-control">
          <option value="Mehrfamilienhaus">Mehrfamilienhaus</option>
          <option value="Einfamilienhaus">Einfamilienhaus</option>
          <option value="Eigentumswohnung">Eigentumswohnung</option>
          <option value="Gewerbeobjekt">Gewerbeobjekt</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">🏠 Kaufdaten</div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Kaufpreis (€) *</label>
        <input type="number" step="0.01" name="purchase_price" class="form-control" placeholder="500.000" value="{{ old('purchase_price') }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Nebenkosten (€) *</label>
        <input type="number" step="0.01" name="closing_costs" class="form-control" placeholder="40.000" value="{{ old('closing_costs') }}" required>
        <div style="font-size:11px; color:#475569; margin-top:3px;">Notar, GrESt, Makler</div>
      </div>
      <div class="form-group">
        <label class="form-label">Renovierungskosten (€)</label>
        <input type="number" step="0.01" name="renovation_costs" class="form-control" placeholder="0" value="{{ old('renovation_costs', 0) }}">
      </div>
      <div class="form-group">
        <label class="form-label">Wohnfläche (m²)</label>
        <input type="number" step="0.01" name="area_sqm" class="form-control" placeholder="200" value="{{ old('area_sqm') }}">
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">💰 Mieteinnahmen</div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Aktuelle Nettokaltmiete/Monat (€) *</label>
        <input type="number" step="0.01" name="rent_net" class="form-control" placeholder="2.500" value="{{ old('rent_net') }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Marktmiete/Monat (Potenzial) (€)</label>
        <input type="number" step="0.01" name="market_rent" class="form-control" placeholder="3.000" value="{{ old('market_rent') }}">
      </div>
      <div class="form-group">
        <label class="form-label">Leerstandsquote (%)</label>
        <input type="number" step="0.1" name="vacancy_rate" class="form-control" placeholder="5" value="{{ old('vacancy_rate', 5) }}">
      </div>
      <div class="form-group">
        <label class="form-label">Bewirtschaftungskosten (% der Bruttomiete)</label>
        <input type="number" step="0.1" name="management_costs_pct" class="form-control" placeholder="10" value="{{ old('management_costs_pct', 10) }}">
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">🏦 Finanzierung</div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Eigenkapital (€) *</label>
        <input type="number" step="0.01" name="equity" class="form-control" placeholder="150.000" value="{{ old('equity') }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Zinssatz (% p.a.) *</label>
        <input type="number" step="0.01" name="loan_rate" class="form-control" placeholder="3.5" value="{{ old('loan_rate', 3.5) }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Tilgungsrate (% p.a.) *</label>
        <input type="number" step="0.01" name="repayment_rate" class="form-control" placeholder="2.0" value="{{ old('repayment_rate', 2) }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Kreditlaufzeit (Jahre)</label>
        <input type="number" name="loan_term_years" class="form-control" placeholder="25" value="{{ old('loan_term_years', 25) }}">
      </div>
    </div>
  </div>

</div>

{{-- Right Panel: Scores --}}
<div class="immo-create-right">
  <div class="card">
    <div class="card-title">🎯 Qualitative Scores</div>

    <div class="form-group">
      <label class="form-label">Lage-Score (1–10) *</label>
      <input type="range" name="location_score" id="loc" min="1" max="10" step="1" value="{{ old('location_score', 7) }}"
        style="width:100%; accent-color:#6366f1;" oninput="document.getElementById('loc_v').textContent=this.value">
      <div style="display:flex; justify-content:space-between; font-size:11px; color:#475569; margin-top:4px;">
        <span>1 — Ländlich</span>
        <span style="font-size:18px; font-weight:700; color:#818cf8;" id="loc_v">{{ old('location_score', 7) }}</span>
        <span>10 — Toplage</span>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Zustand-Score (1–10) *</label>
      <input type="range" name="condition_score" id="cond" min="1" max="10" step="1" value="{{ old('condition_score', 7) }}"
        style="width:100%; accent-color:#6366f1;" oninput="document.getElementById('cond_v').textContent=this.value">
      <div style="display:flex; justify-content:space-between; font-size:11px; color:#475569; margin-top:4px;">
        <span>1 — Sanierungsbedürftig</span>
        <span style="font-size:18px; font-weight:700; color:#818cf8;" id="cond_v">{{ old('condition_score', 7) }}</span>
        <span>10 — Neubau</span>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Mietsteigerungspotenzial (1–10)</label>
      <input type="range" name="rent_growth_score" id="rg" min="1" max="10" step="1" value="{{ old('rent_growth_score', 5) }}"
        style="width:100%; accent-color:#6366f1;" oninput="document.getElementById('rg_v').textContent=this.value">
      <div style="display:flex; justify-content:space-between; font-size:11px; color:#475569; margin-top:4px;">
        <span>1 — Kein Potenzial</span>
        <span style="font-size:18px; font-weight:700; color:#818cf8;" id="rg_v">{{ old('rent_growth_score', 5) }}</span>
        <span>10 — Hoch</span>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">⚖️ KPI-Gewichtung (manuell)</div>
    <div style="font-size:11px; color:#64748b; margin-bottom:10px;">
      Prozentanteile je KPI (0-100). Die Summe darf 100% nicht ueberschreiten.
    </div>
    @foreach([
      ['CASHFLOW', 'Cashflow p.a.', 10, '#10b981'],
      ['CF_RENDITE', 'Cashflow-Rendite', 20, '#10b981'],
      ['DSCR', 'DSCR', 20, '#10b981'],
      ['MIETSTEIGERUNG', 'Mietsteigerung', 15, '#818cf8'],
      ['NETTORENDITE', 'Nettorendite', 10, '#6366f1'],
      ['LTV', 'LTV', 10, '#6366f1'],
      ['MIET_MULTI', 'Mietmultiplikator', 10, '#6366f1'],
      ['LOCATION_SCORE', 'Lage', 10, '#6366f1'],
      ['CONDITION_SCORE', 'Zustand', 5, '#64748b'],
    ] as [$code, $n, $defaultWeight, $c])
    <div style="display:flex; align-items:center; gap:8px; font-size:12px; margin-bottom:6px;">
        <div style="flex:1; color:#94a3b8;">{{ $n }}</div>
        <input
          type="number"
          min="0"
          max="100"
          step="1"
          name="weights[{{ $code }}]"
          value="{{ old('weights.' . $code, $defaultWeight) }}"
          class="form-control"
          style="width:72px; padding:6px 8px; color:{{ $c }}; font-weight:600;"
        >
    </div>
    @endforeach
  </div>

  <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; font-size:14px;">
    🏘 Analyse berechnen & speichern
  </button>
</div>

</div>
</form>
@endsection
