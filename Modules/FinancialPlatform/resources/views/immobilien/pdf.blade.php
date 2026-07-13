<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1e293b; background: #fff; }
    .header { background: linear-gradient(135deg, #1e1b4b, #4c1d95); color: white; padding: 20px 28px; display: table; width: 100%; }
    .header-left { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }
    .logo { font-size: 20px; font-weight: 700; }
    .logo span { opacity: 0.4; font-weight: 300; }
    .sub { font-size: 10px; opacity: 0.55; margin-top: 2px; }

    /* Score Strip */
    .score-strip { display: table; width: 100%; border-bottom: 1px solid #e2e8f0; }
    .sc { display: table-cell; padding: 14px 20px; border-right: 1px solid #e2e8f0; vertical-align: middle; }
    .sc:last-child { border-right: none; }
    .sc-label { font-size: 8.5px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
    .sc-val { font-size: 20px; font-weight: 700; }
    .sc-small { font-size: 13px; font-weight: 600; }
    .green { color: #059669; } .yellow { color: #d97706; } .red { color: #dc2626; }

    .section { margin: 14px 28px; }
    .section-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        color: #7c3aed; margin-bottom: 7px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th { background: #f8fafc; color: #475569; font-size: 8.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .4px; padding: 6px 9px; border: 1px solid #e2e8f0; text-align: left; }
    td { padding: 7px 9px; border: 1px solid #e2e8f0; vertical-align: middle; }
    tr:nth-child(even) td { background: #faf5ff; }
    .tl-green { color: #059669; font-weight: 700; }
    .tl-yellow { color: #d97706; font-weight: 700; }
    .tl-red { color: #dc2626; font-weight: 700; }

    /* Cashflow box */
    .cf-box { display: table; width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 12px; }
    .cf-cell { display: table-cell; padding: 12px 16px; border-right: 1px solid #e2e8f0; text-align: center; }
    .cf-cell:last-child { border-right: none; }
    .cf-label { font-size: 8.5px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
    .cf-val { font-size: 15px; font-weight: 700; }

    .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f8fafc;
        border-top: 1px solid #e2e8f0; padding: 7px 28px; display: table; width: 100%; font-size: 9px; color: #94a3b8; }
    .footer-left { display: table-cell; }
    .footer-right { display: table-cell; text-align: right; }
    .disclaimer { margin: 12px 28px 40px; padding: 9px 12px; background: #fefce8;
        border: 1px solid #fde68a; border-radius: 5px; font-size: 9px; color: #92400e; line-height: 1.6; }
</style>
</head>
<body>

@php
    $score = $analysis->total_score ?? 0;
    $color = $analysis->scoreColor();
    $input = $analysis->immobilienInput;
    $colorHex = ['green'=>'#059669','yellow'=>'#d97706','red'=>'#dc2626','gray'=>'#64748b'][$color];
@endphp

{{-- HEADER --}}
<div class="header">
    <div class="header-left">
        <div class="logo">⬡ Allocore <span>Financial</span></div>
        <div class="sub">Immobilienanalyse · {{ $analysis->name }}</div>
    </div>
    <div class="header-right">
        <strong>{{ $analysis->company->name ?? '—' }}</strong>
        <div style="font-size:9px; opacity:.5; margin-top:2px;">Erstellt: {{ $analysis->created_at->format('d.m.Y') }}</div>
    </div>
</div>

{{-- SCORE STRIP --}}
<div class="score-strip">
    <div class="sc">
        <div class="sc-label">Gesamt-Score</div>
        <div class="sc-val {{ $color }}">{{ number_format($score, 1) }}<span style="font-size:12px; color:#94a3b8;">/100</span></div>
    </div>
    <div class="sc">
        <div class="sc-label">Objekttyp</div>
        <div class="sc-small">{{ $input->property_type ?? 'Immobilie' }}</div>
    </div>
    <div class="sc">
        <div class="sc-label">Kaufpreis</div>
        <div class="sc-small">{{ number_format($input->purchase_price ?? 0, 0, ',', '.') }} €</div>
    </div>
    <div class="sc">
        <div class="sc-label">Eigenkapital</div>
        <div class="sc-small">{{ number_format($input->equity ?? 0, 0, ',', '.') }} €</div>
    </div>
    <div class="sc" colspan="2">
        <div class="sc-label">Empfehlung</div>
        <div class="sc-small" style="color:{{ $colorHex }};">{{ $analysis->recommendation }}</div>
    </div>
</div>

{{-- CASHFLOW SUMMARY --}}
<div class="section" style="margin-top:14px;">
    <div class="section-title">Cashflow-Übersicht (p.a.)</div>
    <div class="cf-box">
        <div class="cf-cell">
            <div class="cf-label">Gesamtinvestition</div>
            <div class="cf-val">{{ number_format($derived['gesamtinvestition'], 0, ',', '.') }} €</div>
        </div>
        <div class="cf-cell">
            <div class="cf-label">Darlehen</div>
            <div class="cf-val">{{ number_format($derived['darlehen'], 0, ',', '.') }} €</div>
        </div>
        <div class="cf-cell">
            <div class="cf-label">NOI p.a.</div>
            <div class="cf-val">{{ number_format($derived['noi'], 0, ',', '.') }} €</div>
        </div>
        <div class="cf-cell">
            <div class="cf-label">Schuldendienst</div>
            <div class="cf-val" style="color:#dc2626;">{{ number_format($derived['schuldendienst'], 0, ',', '.') }} €</div>
        </div>
        <div class="cf-cell">
            <div class="cf-label">Cashflow p.a.</div>
            @php $cf = $derived['cashflow']; @endphp
            <div class="cf-val" style="color:{{ $cf >= 0 ? '#059669' : '#dc2626' }};">
                {{ ($cf >= 0 ? '+' : '') . number_format($cf, 0, ',', '.') }} €
            </div>
        </div>
    </div>
</div>

{{-- KPI TABLE --}}
<div class="section">
    <div class="section-title">KPI-Ergebnisse</div>
    <table>
        <thead>
            <tr>
                <th>KPI</th>
                <th>Wert</th>
                <th>Einheit</th>
                <th>Gewicht</th>
                <th>Bewertung</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analysis->kpiResults->unique('kpi_code') as $kpi)
            <tr>
                <td><strong>{{ $kpi->kpi_name }}</strong></td>
                <td style="text-align:right;">{{ number_format((float)$kpi->value, 2, ',', '.') }}</td>
                <td style="color:#64748b;">{{ $kpi->unit }}</td>
                <td>{{ $kpi->weight > 0 ? $kpi->weight.'%' : '—' }}</td>
                <td class="tl-{{ $kpi->traffic_light }}">
                    {{ $kpi->traffic_light==='green' ? '● Gut' : ($kpi->traffic_light==='yellow' ? '● Mittel' : '● Kritisch') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- OBJECT DETAILS --}}
@if($input)
<div class="section">
    <div class="section-title">Objektdetails & Finanzierung</div>
    <table>
        <tbody>
            <tr><td>Wohnfläche</td><td>{{ ($input->area_sqm ?? '—') }} m²</td>
                <td>Nettokaltmiete/Monat</td><td>{{ number_format($input->rent_net ?? 0, 0,',','.') }} €</td></tr>
            <tr><td>Marktmiete/Monat</td><td>{{ number_format($input->market_rent ?? 0, 0,',','.') }} €</td>
                <td>Leerstandsquote</td><td>{{ $input->vacancy_rate }} %</td></tr>
            <tr><td>Zinssatz p.a.</td><td>{{ $input->loan_rate }} %</td>
                <td>Tilgungsrate p.a.</td><td>{{ $input->repayment_rate }} %</td></tr>
            <tr><td>Lage-Score</td><td>{{ ($input->location_score ?? '—') }}/10</td>
                <td>Zustand-Score</td><td>{{ ($input->condition_score ?? '—') }}/10</td></tr>
            <tr><td>Nebenkosten</td><td>{{ number_format($input->closing_costs ?? 0, 0,',','.') }} €</td>
                <td>Renovierungskosten</td><td>{{ number_format($input->renovation_costs ?? 0, 0,',','.') }} €</td></tr>
        </tbody>
    </table>
</div>
@endif

<div class="disclaimer">
    <strong>Hinweis:</strong> Diese Immobilienanalyse basiert auf den eingegebenen Daten und stellt keine Anlageberatung dar.
    Alle Berechnungen wurden automatisch durchgeführt. Erstellt via Allocore Financial Platform, {{ now()->format('d.m.Y H:i') }}.
</div>

<div class="footer">
    <div class="footer-left">Allocore · Immobilienanalyse · {{ $analysis->name }}</div>
    <div class="footer-right">Score: {{ number_format($score,1) }}/100 · {{ now()->format('d.m.Y') }}</div>
</div>

</body>
</html>
