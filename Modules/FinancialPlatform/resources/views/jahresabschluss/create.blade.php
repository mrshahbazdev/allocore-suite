@extends('layouts.shell')
@section('title', 'Jahresabschluss erstellen — Allocore')
@section('page-title', '📈 Jahresabschluss — Neue Analyse')
@section('topbar-actions')
    <a href="{{ route('jahresabschluss.index') }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection
@push('styles')
<style>
    .ja-tabs {
        margin-bottom: 8px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .ja-year-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 1024px) {
        .ja-year-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 640px) {
        .ja-year-grid {
            grid-template-columns: 1fr;
        }
        .ja-tab-btn {
            flex: 1 1 calc(50% - 8px);
            text-align: center;
        }
    }
</style>
@endpush
@section('content')

<form method="POST" action="{{ route('jahresabschluss.store') }}">
@csrf

{{-- Basic Info --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-title">📋 Grunddaten</div>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Name der Analyse *</label>
            <input type="text" name="name" class="form-control" placeholder="Jahresabschluss 2024" value="{{ old('name') }}" required>
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
    </div>
</div>

{{-- 3 Year Tabs --}}
<div class="ja-tabs">
    @foreach([0,1,2] as $i)
    <button type="button" onclick="showYear({{ $i }})" id="tab-{{ $i }}" class="ja-tab-btn"
        style="padding:8px 20px; border-radius:8px; border:1px solid rgba(99,102,241,0.3);
        background:{{ $i===0?'rgba(99,102,241,0.2)':'transparent' }};
        color:{{ $i===0?'#818cf8':'#64748b' }}; cursor:pointer; font-size:13px; font-family:inherit;">
        Jahr {{ $i+1 }}
    </button>
    @endforeach
</div>

@foreach([0,1,2] as $i)
<div id="year-panel-{{ $i }}" style="{{ $i!==0?'display:none;':'' }}">
<div class="card" style="margin-bottom:16px;">
    <div class="card-title">📅 Jahr {{ $i+1 }}</div>
    <input type="hidden" name="years[{{ $i }}][year_order]" value="{{ $i+1 }}">
    <div class="form-group">
        <label class="form-label">Jahrbezeichnung *</label>
        <input type="text" name="years[{{ $i }}][year_label]" class="form-control"
            placeholder="{{ date('Y') - (2-$i) }}" value="{{ old("years.$i.year_label", date('Y') - (2-$i)) }}" required>
    </div>
    <div class="ja-year-grid">
        <div class="form-group"><label class="form-label">Umsatz (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][revenue]" class="form-control" placeholder="2.000.000" value="{{ old("years.$i.revenue") }}"></div>
        <div class="form-group"><label class="form-label">EBIT (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][ebit]" class="form-control" placeholder="200.000" value="{{ old("years.$i.ebit") }}"></div>
        <div class="form-group"><label class="form-label">Jahresüberschuss (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][net_profit]" class="form-control" placeholder="120.000" value="{{ old("years.$i.net_profit") }}"></div>
        <div class="form-group"><label class="form-label">Eigenkapital (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][equity]" class="form-control" placeholder="600.000" value="{{ old("years.$i.equity") }}"></div>
        <div class="form-group"><label class="form-label">Bilanzsumme (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][total_assets]" class="form-control" placeholder="1.500.000" value="{{ old("years.$i.total_assets") }}"></div>
        <div class="form-group"><label class="form-label">Umlaufvermögen (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][current_assets]" class="form-control" placeholder="700.000" value="{{ old("years.$i.current_assets") }}"></div>
        <div class="form-group"><label class="form-label">Kasse / Cash (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][cash]" class="form-control" placeholder="200.000" value="{{ old("years.$i.cash") }}"></div>
        <div class="form-group"><label class="form-label">Forderungen (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][receivables]" class="form-control" placeholder="300.000" value="{{ old("years.$i.receivables") }}"></div>
        <div class="form-group"><label class="form-label">Vorräte (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][inventory]" class="form-control" placeholder="100.000" value="{{ old("years.$i.inventory") }}"></div>
        <div class="form-group"><label class="form-label">Kurzfr. Verbindlichkeiten (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][current_liabilities]" class="form-control" placeholder="400.000" value="{{ old("years.$i.current_liabilities") }}"></div>
        <div class="form-group"><label class="form-label">Gesamtverbindlichkeiten (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][total_liabilities]" class="form-control" placeholder="900.000" value="{{ old("years.$i.total_liabilities") }}"></div>
        <div class="form-group"><label class="form-label">Zinsaufwand (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][interest_exp]" class="form-control" placeholder="30.000" value="{{ old("years.$i.interest_exp") }}"></div>
        <div class="form-group"><label class="form-label">Materialaufwand (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][material_costs]" class="form-control" placeholder="500.000" value="{{ old("years.$i.material_costs") }}"></div>
        <div class="form-group"><label class="form-label">Personalaufwand (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][personnel_costs]" class="form-control" placeholder="400.000" value="{{ old("years.$i.personnel_costs") }}"></div>
        <div class="form-group"><label class="form-label">Verbindlichkeiten L+L (€)</label>
            <input type="number" step="0.01" name="years[{{ $i }}][payables]" class="form-control" placeholder="150.000" value="{{ old("years.$i.payables") }}"></div>
    </div>
</div>
</div>
@endforeach

<button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; font-size:14px;">
    📈 Jahresabschluss berechnen & speichern
</button>

</form>

@push('scripts')
<script>
function showYear(i) {
    [0,1,2].forEach(j => {
        document.getElementById('year-panel-'+j).style.display = j===i?'':'none';
        const t = document.getElementById('tab-'+j);
        t.style.background = j===i?'rgba(99,102,241,0.2)':'transparent';
        t.style.color = j===i?'#818cf8':'#64748b';
    });
}
</script>
@endpush

@endsection
