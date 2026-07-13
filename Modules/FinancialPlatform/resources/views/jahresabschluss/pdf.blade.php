<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1e293b; background: #fff; }
    .header { background: linear-gradient(135deg, #1e1b4b, #312e81); color: white; padding: 20px 28px; display: table; width: 100%; }
    .header-left { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }
    .logo { font-size: 20px; font-weight: 700; }
    .logo span { opacity: 0.4; font-weight: 300; }
    .sub { font-size: 10px; opacity: 0.55; margin-top: 2px; }

    .section { margin: 14px 28px; }
    .section-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        color: #6366f1; margin-bottom: 7px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }

    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th { background: #f8fafc; color: #475569; font-size: 8.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .4px; padding: 6px 9px; border: 1px solid #e2e8f0; text-align: left; }
    td { padding: 7px 9px; border: 1px solid #e2e8f0; vertical-align: middle; }
    tr:nth-child(even) td { background: #f8fafc; }
    .tl-green { color: #059669; font-weight: 700; }
    .tl-yellow { color: #d97706; font-weight: 700; }
    .tl-red { color: #dc2626; font-weight: 700; }

    /* Year header cells */
    .year-header { background: #312e81 !important; color: white !important; text-align: center; }

    /* Bericht */
    .bericht-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 12px 14px; line-height: 1.7; font-size: 11px; color: #0c4a6e; margin-bottom: 12px; }

    .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f8fafc;
        border-top: 1px solid #e2e8f0; padding: 7px 28px; display: table; width: 100%; font-size: 9px; color: #94a3b8; }
    .footer-left { display: table-cell; }
    .footer-right { display: table-cell; text-align: right; }
    .disclaimer { margin: 12px 28px 40px; padding: 9px 12px; background: #fefce8;
        border: 1px solid #fde68a; border-radius: 5px; font-size: 9px; color: #92400e; line-height: 1.6; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <div class="header-left">
        <div class="logo">⬡ Allocore <span>Financial</span></div>
        <div class="sub">Jahresabschluss-Analyse · {{ $analysis->name }}</div>
    </div>
    <div class="header-right">
        <strong>{{ $analysis->company->name ?? '—' }}</strong>
        <div style="font-size:9px; opacity:.5; margin-top:2px;">Erstellt: {{ $analysis->created_at->format('d.m.Y') }}</div>
    </div>
</div>

@php
    $years = $analysis->jahresabschlussInputs->sortBy('year_order')->values();
    $kpiResults = $analysis->kpiResults;
    $kpiCodes = $kpiResults->pluck('kpi_code')->unique()->values();
@endphp

{{-- AUTO BERICHT --}}
@if(!empty($bericht))
<div class="section">
    <div class="section-title">Automatischer Analyse-Bericht</div>
    <div class="bericht-box">{{ $bericht }}</div>
</div>
@endif

{{-- MULTI-YEAR KPI TABLE --}}
<div class="section">
    <div class="section-title">KPI-Übersicht — Mehrjahresvergleich</div>
    <table>
        <thead>
            <tr>
                <th style="width:180px;">Kennzahl</th>
                <th style="width:50px;">Einheit</th>
                @foreach($years as $y)
                    <th class="year-header">{{ $y->year_label }}</th>
                    <th style="text-align:center; width:60px;">Ampel</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($kpiCodes as $code)
            @php $kpiName = $kpiResults->where('kpi_code', $code)->first()?->kpi_name; @endphp
            <tr>
                <td><strong>{{ $kpiName }}</strong></td>
                <td style="color:#64748b;">{{ $kpiResults->where('kpi_code',$code)->first()?->unit }}</td>
                @foreach($years as $y)
                @php $k = $kpiResults->where('kpi_code',$code)->where('year_label',$y->year_label)->first(); @endphp
                <td style="text-align:center; font-weight:600;">
                    {{ $k ? number_format((float)$k->value, 2, ',', '.') : '—' }}
                </td>
                <td class="{{ $k ? 'tl-'.$k->traffic_light : '' }}" style="text-align:center; font-size:10px;">
                    {{ $k ? ($k->traffic_light==='green' ? '● Gut' : ($k->traffic_light==='yellow' ? '● Mittel' : '● Kritisch')) : '—' }}
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- YEAR-BY-YEAR BILANZ --}}
<div class="section">
    <div class="section-title">Rohdaten — Bilanz & GuV</div>
    <table>
        <thead>
            <tr>
                <th>Position</th>
                @foreach($years as $y)<th class="year-header">{{ $y->year_label }} (€)</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @foreach([
                ['Umsatz', 'revenue'],
                ['EBIT', 'ebit'],
                ['Jahresüberschuss', 'net_profit'],
                ['Eigenkapital', 'equity'],
                ['Bilanzsumme', 'total_assets'],
                ['Umlaufvermögen', 'current_assets'],
                ['Kasse / Cash', 'cash'],
                ['Forderungen', 'receivables'],
                ['Vorräte', 'inventory'],
                ['Kurzfr. Verbindlichkeiten', 'current_liabilities'],
                ['Gesamtverbindlichkeiten', 'total_liabilities'],
                ['Zinsaufwand', 'interest_exp'],
                ['Materialaufwand', 'material_costs'],
                ['Personalaufwand', 'personnel_costs'],
            ] as [$label, $field])
            <tr>
                <td>{{ $label }}</td>
                @foreach($years as $y)
                <td style="text-align:right;">
                    {{ $y->$field !== null ? number_format((float)$y->$field, 0, ',', '.') : '—' }}
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="disclaimer">
    <strong>Hinweis:</strong> Diese Analyse basiert auf den eingegebenen Daten. Alle KPIs wurden automatisch berechnet.
    Keine Anlageberatung. Erstellt via Allocore Financial Platform, {{ now()->format('d.m.Y H:i') }}.
</div>

<div class="footer">
    <div class="footer-left">Allocore · Jahresabschluss · {{ $analysis->name }}</div>
    <div class="footer-right">{{ now()->format('d.m.Y') }}</div>
</div>

</body>
</html>
