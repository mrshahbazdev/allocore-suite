@extends('layouts.shell')
@section('title', 'Excel Import — Allocore')
@section('page-title', '📥 Excel Import')

@push('styles')
<style>
    .import-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
        align-items: start;
    }
    .import-side-stack {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .import-hint-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 900px) {
        .import-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="import-layout">

  {{-- Upload Form --}}
  <div class="card">
    <div class="card-title">📤 Excel-Datei hochladen</div>

    <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
      <label class="form-label">Analyse-Typ *</label>
      <select name="type" id="import-type" class="form-control" required onchange="updateHint(this.value)">
        <option value="">— Bitte wählen —</option>
        <option value="gmbh">📊 GmbH Analyse</option>
        <option value="jahresabschluss">📈 Jahresabschluss (bis 3 Jahre)</option>
        <option value="immobilien">🏘 Immobilienanalyse</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">Unternehmen *</label>
      <select name="company_id" class="form-control" required>
        <option value="">— Bitte wählen —</option>
        @foreach($companies as $c)
          <option value="{{ $c->id }}">{{ $c->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">Name der Analyse *</label>
      <input type="text" name="name" class="form-control" placeholder="Excel Import 2024" required>
    </div>

    {{-- Drop Zone --}}
    <div class="form-group">
      <label class="form-label">Excel-Datei (.xlsx / .xls) *</label>
      <div id="drop-zone" style="border:2px dashed rgba(99,102,241,0.3); border-radius:12px; padding:40px 20px; text-align:center; cursor:pointer; transition:all .2s; position:relative;"
           ondragover="this.style.borderColor='#6366f1'; this.style.background='rgba(99,102,241,0.08)'; event.preventDefault();"
           ondragleave="this.style.borderColor='rgba(99,102,241,0.3)'; this.style.background='';"
           ondrop="handleDrop(event)"
           onclick="document.getElementById('file-input').click()">
        <div style="font-size:32px; margin-bottom:10px;">📂</div>
        <div style="font-size:14px; color:#94a3b8; margin-bottom:4px;">Datei hier hinziehen</div>
        <div style="font-size:12px; color:#475569;">oder klicken zum Auswählen</div>
        <div id="file-name" style="margin-top:12px; font-size:12px; color:#818cf8; font-weight:500;"></div>
      </div>
      <input type="file" id="file-input" name="file" accept=".xlsx,.xls,.csv" style="display:none" onchange="showFileName(this)">
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:13px; font-size:14px;">
      📥 Importieren & Berechnen
    </button>
    </form>
  </div>

  {{-- Right Panel --}}
  <div class="import-side-stack">

    {{-- Templates Download --}}
    <div class="card">
      <div class="card-title">📄 Vorlagen herunterladen</div>
      <div style="font-size:12px; color:#64748b; margin-bottom:14px; line-height:1.6;">
        Laden Sie die passende Excel-Vorlage herunter, füllen Sie die Felder aus und laden Sie sie hoch.
      </div>
      <div style="display:flex; flex-direction:column; gap:8px;">
        <a href="{{ route('import.template', 'gmbh') }}" class="btn btn-secondary" style="justify-content:center; font-size:12px;">
          ⬇ GmbH Vorlage (.xlsx)
        </a>
        <a href="{{ route('import.template', 'jahresabschluss') }}" class="btn btn-secondary" style="justify-content:center; font-size:12px;">
          ⬇ Jahresabschluss Vorlage (.xlsx)
        </a>
        <a href="{{ route('import.template', 'immobilien') }}" class="btn btn-secondary" style="justify-content:center; font-size:12px;">
          ⬇ Immobilien Vorlage (.xlsx)
        </a>
      </div>
    </div>

    {{-- Column Hints --}}
    <div class="card" id="hint-box">
      <div class="card-title" id="hint-title">📋 Spalteninfo</div>
      <div id="hint-gmbh" style="display:none;">
        <div class="import-hint-table-wrap">
        <table class="data-table" style="font-size:11px;">
          <thead><tr><th>Spalte</th><th>Feldname</th></tr></thead>
          <tbody>
            @foreach(['A'=>'revenue_current','B'=>'revenue_prev','C'=>'ebitda','D'=>'net_profit','E'=>'equity','F'=>'total_debt','G'=>'total_assets','H'=>'current_assets','I'=>'current_liabilities','J'=>'cash','K'=>'monthly_burn','P'=>'mgmt_score','Q'=>'market_score'] as $col=>$field)
            <tr><td style="color:#818cf8; font-weight:700;">{{ $col }}</td><td>{{ $field }}</td></tr>
            @endforeach
          </tbody>
        </table>
        </div>
      </div>
      <div id="hint-jahresabschluss" style="display:none;">
        <div style="font-size:11px; color:#64748b; line-height:1.7;">
          Jede <strong style="color:#fbbf24;">Zeile = 1 Jahr</strong> (max. 3 Zeilen).<br>
          Pflichtfelder: <code style="color:#818cf8;">year_label, equity, total_assets, revenue, ebit, net_profit</code>
        </div>
      </div>
      <div id="hint-immobilien" style="display:none;">
        <div style="font-size:11px; color:#64748b; line-height:1.7;">
          <strong style="color:#c084fc;">Eine Zeile</strong> pro Objekt.<br>
          Pflichtfelder: <code style="color:#818cf8;">purchase_price, equity, rent_net, loan_rate, repayment_rate, location_score, condition_score</code>
        </div>
      </div>
      <div id="hint-default" style="font-size:12px; color:#475569;">Wählen Sie einen Typ, um die Spalteninfo zu sehen.</div>
    </div>

    {{-- Instructions --}}
    <div class="card">
      <div class="card-title">ℹ️ So funktioniert's</div>
      <div style="display:flex; flex-direction:column; gap:10px;">
        @foreach(['Vorlage herunterladen (Excel-Template)', 'Daten in Tabellenblatt eintragen', 'Typ & Unternehmen auswählen', 'Datei hochladen — Score wird sofort berechnet'] as $i=>$step)
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <div style="width:22px; height:22px; border-radius:50%; background:rgba(99,102,241,0.2); border:1px solid rgba(99,102,241,0.4); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:#818cf8; flex-shrink:0;">{{ $i+1 }}</div>
          <div style="font-size:12px; color:#94a3b8; padding-top:2px;">{{ $step }}</div>
        </div>
        @endforeach
      </div>
    </div>

  </div>
</div>

@push('scripts')
<script>
function showFileName(input) {
    document.getElementById('file-name').textContent = input.files[0]?.name ?? '';
}
function handleDrop(e) {
    e.preventDefault();
    const dt = e.dataTransfer;
    if (dt.files.length) {
        document.getElementById('file-input').files = dt.files;
        document.getElementById('file-name').textContent = dt.files[0].name;
    }
    e.target.closest('#drop-zone').style.borderColor = 'rgba(99,102,241,0.3)';
    e.target.closest('#drop-zone').style.background = '';
}
function updateHint(type) {
    ['gmbh','jahresabschluss','immobilien','default'].forEach(t => {
        document.getElementById('hint-'+t).style.display = t===type || (t==='default'&&!type) ? 'block' : 'none';
    });
}
</script>
@endpush

@endsection
