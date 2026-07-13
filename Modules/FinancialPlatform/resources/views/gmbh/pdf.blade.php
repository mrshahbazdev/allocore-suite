<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 12mm 10mm 12mm 10mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1e293b; background: #fff; }

    /* ─── Header ─── */
    .header {
        background: linear-gradient(135deg, #1e1b4b, #312e81);
        color: white; padding: 18px 16px;
        display: table; width: 100%;
    }
    .header-left { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }
    .logo { font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
    .logo span { opacity: 0.5; font-weight: 300; }
    .report-type { font-size: 11px; opacity: 0.6; margin-top: 2px; }
    .header-right .date { font-size: 10px; opacity: 0.5; margin-top: 3px; }

    /* ─── Score Box ─── */
    .score-box {
        margin: 14px 0;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    .score-cell {
        display: table-cell;
        padding: 12px 10px;
        vertical-align: middle;
        border-right: 1px solid #e2e8f0;
        text-align: center;
        overflow-wrap: anywhere;
        word-wrap: break-word;
    }
    .score-cell:last-child { border-right: none; }
    .score-num { font-size: 34px; font-weight: 700; }
    .score-green { color: #059669; }
    .score-yellow { color: #d97706; }
    .score-red { color: #dc2626; }
    .score-label { font-size: 10px; color: #64748b; margin-top: 2px; }
    .meta-label { font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
    .meta-val { font-size: 12px; font-weight: 600; color: #1e293b; word-break: break-word; }
    .recommend-text { font-size: 11px; font-weight: 600; word-break: break-word; }

    /* ─── Section ─── */
    .section { margin: 12px 0; }
    .section-title {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 1px; color: #6366f1; margin-bottom: 8px;
        padding-bottom: 5px; border-bottom: 1px solid #e2e8f0;
    }

    /* ─── Tables ─── */
    table { width: 100%; border-collapse: collapse; font-size: 10px; table-layout: fixed; }
    th { background: #f8fafc; color: #64748b; font-size: 9px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .5px; padding: 7px 10px; text-align: left;
        border: 1px solid #e2e8f0; }
    td { padding: 7px 8px; border: 1px solid #e2e8f0; vertical-align: middle; word-break: break-word; }
    tr:nth-child(even) td { background: #f8fafc; }

    /* ─── Traffic lights ─── */
    .tl-green { color: #059669; font-weight: 600; }
    .tl-yellow { color: #d97706; font-weight: 600; }
    .tl-red { color: #dc2626; font-weight: 600; }

    /* ─── Progress bar ─── */
    .bar-wrap { background: #e2e8f0; border-radius: 3px; height: 5px; width: 80px; display: inline-block; vertical-align: middle; margin-left: 6px; }
    .bar-fill { height: 5px; border-radius: 3px; }

    /* ─── Two columns ─── */
    .two-col { display: table; width: 100%; }
    .col { display: table-cell; vertical-align: top; padding-right: 10px; }
    .col:last-child { padding-right: 0; padding-left: 10px; }

    /* ─── Footer ─── */
    .footer {
        position: static;
        background: #f8fafc; border-top: 1px solid #e2e8f0;
        padding: 8px 10px; display: table; width: 100%;
        font-size: 9px; color: #94a3b8;
        margin-top: 12px;
    }
    .footer-left { display: table-cell; }
    .footer-right { display: table-cell; text-align: right; }

    /* ─── Disclaimer ─── */
    .disclaimer {
        margin: 12px 0 0;
        padding: 10px 14px;
        background: #fefce8; border: 1px solid #fde68a; border-radius: 6px;
        font-size: 9px; color: #92400e; line-height: 1.6;
    }
    .glossary-note {
        margin: 10px 0 0;
        padding: 8px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 9px;
        color: #475569;
        line-height: 1.5;
    }
    .glossary-note strong { color: #1e293b; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <div class="header-left">
        <div class="logo">⬡ Allocore <span>Financial</span></div>
        <div class="report-type">GmbH Analyse Report</div>
    </div>
    <div class="header-right">
        <strong>{{ $analysis->name }}</strong>
        <div class="date">Erstellt: {{ $analysis->created_at->format('d.m.Y') }}</div>
    </div>
</div>

{{-- SCORE BOX --}}
@php
    $score = $analysis->total_score ?? 0;
    $color = $analysis->scoreColor();
    $input = $analysis->gmbhInput;
@endphp
<div class="score-box">
    <div class="score-cell">
        <div class="score-num score-{{ $color }}">{{ number_format($score, 1) }}</div>
        <div class="score-label">/ 100 Punkte</div>
    </div>
    <div class="score-cell">
        <div class="meta-label">Unternehmen</div>
        <div class="meta-val">{{ $analysis->company->name ?? '—' }}</div>
    </div>
    <div class="score-cell">
        <div class="meta-label">Branche</div>
        <div class="meta-val">{{ $analysis->company->industry ?? '—' }}</div>
    </div>
    <div class="score-cell">
        <div class="meta-label">Empfehlung</div>
        <div class="recommend-text class-{{ $color }}" style="color:{{ $color==='green'?'#059669':($color==='yellow'?'#d97706':'#dc2626') }}">
            {{ $analysis->recommendation }}
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
                <th>Score-Beitrag</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analysis->kpiResults->unique('kpi_code') as $kpi)
            @php
                $scoreContrib = $kpi->weight > 0 ? number_format(($score * $kpi->weight / 100), 1) : '—';
            @endphp
            <tr>
                <td><strong>{{ $kpi->kpi_name }}</strong></td>
                <td>{{ number_format((float)$kpi->value, 2, ',', '.') }}</td>
                <td>{{ $kpi->unit }}</td>
                <td>{{ $kpi->weight > 0 ? $kpi->weight . '%' : '—' }}</td>
                <td class="tl-{{ $kpi->traffic_light }}">
                    {{ $kpi->traffic_light === 'green' ? '● Gut' : ($kpi->traffic_light === 'yellow' ? '● Mittel' : '● Kritisch') }}
                </td>
                <td>
                    @if($kpi->weight > 0)
                    <div class="bar-wrap">
                        <div class="bar-fill" style="width:{{ min(100, $score) }}%; background:{{ $kpi->traffic_light==='green'?'#059669':($kpi->traffic_light==='yellow'?'#d97706':'#dc2626') }};"></div>
                    </div>
                    @else —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- INPUT VALUES --}}
@if($input)
<div class="section">
    <div class="section-title">Eingabedaten</div>
    <div class="two-col">
        <div class="col">
            <table>
                <tbody>
                    <tr><td>Umsatz aktuell</td><td><strong>{{ number_format($input->revenue_current ?? 0, 0, ',', '.') }} €</strong></td></tr>
                    <tr><td>Umsatz Vorjahr</td><td>{{ number_format($input->revenue_prev ?? 0, 0, ',', '.') }} €</td></tr>
                    <tr><td>EBITDA</td><td>{{ number_format($input->ebitda ?? 0, 0, ',', '.') }} €</td></tr>
                    <tr><td>Jahresüberschuss</td><td>{{ number_format($input->net_profit ?? 0, 0, ',', '.') }} €</td></tr>
                    <tr><td>Eigenkapital</td><td>{{ number_format($input->equity ?? 0, 0, ',', '.') }} €</td></tr>
                    <tr><td>Gesamtverbindlichkeiten</td><td>{{ number_format($input->total_debt ?? 0, 0, ',', '.') }} €</td></tr>
                </tbody>
            </table>
        </div>
        <div class="col">
            <table>
                <tbody>
                    <tr><td>Bilanzsumme</td><td>{{ number_format($input->total_assets ?? 0, 0, ',', '.') }} €</td></tr>
                    <tr><td>Umlaufvermögen</td><td>{{ number_format($input->current_assets ?? 0, 0, ',', '.') }} €</td></tr>
                    <tr><td>Kurzfr. Verbindlichkeiten</td><td>{{ number_format($input->current_liabilities ?? 0, 0, ',', '.') }} €</td></tr>
                    <tr><td>Cash / Kasse</td><td>{{ number_format($input->cash ?? 0, 0, ',', '.') }} €</td></tr>
                    <tr><td>Management-Score</td><td>{{ ($input->mgmt_score ?? '—') }}/10</td></tr>
                    <tr><td>Markt-Score</td><td>{{ ($input->market_score ?? '—') }}/10</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="glossary-note">
    <strong>Kurz-Glossar:</strong>
    EBIT = operativer Gewinn vor Zinsen/Steuern,
    EBITDA = EBIT plus Abschreibungen,
    Current Ratio = Umlaufvermoegen / kurzfristige Verbindlichkeiten,
    Debt/Equity = Schulden / Eigenkapital.
</div>

{{-- DISCLAIMER --}}
<div class="disclaimer">
    <strong>Hinweis:</strong> Diese Analyse basiert auf den eingegebenen Daten und wurde automatisch berechnet.
    Sie stellt keine Anlageberatung oder Finanzierungszusage dar. Alle Angaben ohne Gewähr.
    Erstellt via Allocore Financial Platform am {{ now()->format('d.m.Y H:i') }}.
</div>

{{-- FOOTER --}}
<div class="footer">
    <div class="footer-left">Allocore Financial Platform · GmbH Analyse · {{ $analysis->name }}</div>
    <div class="footer-right">Score: {{ number_format($score, 1) }}/100 · {{ now()->format('d.m.Y') }}</div>
</div>

</body>
</html>
