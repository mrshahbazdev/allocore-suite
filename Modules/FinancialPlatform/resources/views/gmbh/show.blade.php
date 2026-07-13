@extends('layouts.shell')

@section('title', 'GmbH Analyse — ' . $analysis->name)
@section('page-title', '📊 ' . $analysis->name)

@section('topbar-actions')
    <a href="{{ route('gmbh.pdf', $analysis) }}" class="btn btn-secondary btn-sm">⬇ PDF Export</a>
    <a href="{{ route('gmbh.edit', $analysis) }}" class="btn btn-secondary btn-sm">✏ Bearbeiten</a>
    <a href="{{ route('gmbh.index') }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection

@push('styles')
<style>
    .gmbh-show-top-grid {
        display: grid;
        grid-template-columns: 220px 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    .gmbh-show-kpi-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    .gmbh-show-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 1100px) {
        .gmbh-show-top-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 900px) {
        .gmbh-show-kpi-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

@php
    $score = $analysis->total_score ?? 0;
    $color = $analysis->scoreColor();
    $colorHex = ['green' => '#10b981', 'yellow' => '#f59e0b', 'red' => '#ef4444', 'gray' => '#64748b'][$color];
    $input = $analysis->gmbhInput;
@endphp

{{-- Top Row: Score + Company + Recommendation --}}
<div class="gmbh-show-top-grid">

    {{-- Score Gauge --}}
    <div class="card" style="text-align:center; padding:28px 20px; border-color:{{ $colorHex }}40;">
        <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px;">Gesamt-Score</div>
        <div class="score-lg score-{{ $color }}">{{ number_format($score, 1) }}</div>
        <div style="font-size:14px; color:#475569; margin-top:4px;">/100 Punkte</div>
        <div style="margin-top:16px;">
            <span class="badge badge-{{ $color }}">
                @if($color === 'green') ✅ Positiv
                @elseif($color === 'yellow') ⚠ Mittelmäßig
                @else ❌ Kritisch
                @endif
            </span>
        </div>
        {{-- Progress Bar --}}
        <div style="margin-top:16px; height:6px; background:rgba(255,255,255,0.08); border-radius:3px; overflow:hidden;">
            <div style="height:100%; width:{{ $score }}%; background:{{ $colorHex }}; border-radius:3px; transition:width .5s;"></div>
        </div>
    </div>

    {{-- Company Info --}}
    <div class="card">
        <div class="card-title">🏢 Unternehmen</div>
        <div style="font-size:20px; font-weight:600; color:#e2e8f0; margin-bottom:8px;">{{ $analysis->company->name }}</div>
        <div style="display:flex; flex-direction:column; gap:6px;">
            <div style="font-size:13px; color:#94a3b8;">
                <span style="color:#64748b;">Branche:</span> {{ $analysis->company->industry ?? '—' }}
            </div>
            <div style="font-size:13px; color:#94a3b8;">
                <span style="color:#64748b;">Währung:</span> {{ $analysis->company->currency }}
            </div>
            <div style="font-size:13px; color:#94a3b8;">
                <span style="color:#64748b;">Erstellt:</span> {{ $analysis->created_at->format('d.m.Y H:i') }}
            </div>
            <div style="font-size:13px; color:#94a3b8;">
                <span style="color:#64748b;">Status:</span>
                <span class="badge badge-green">{{ ucfirst($analysis->status) }}</span>
            </div>
        </div>
    </div>

    {{-- Recommendation --}}
    <div class="card" style="border-color:{{ $colorHex }}30; background:{{ $colorHex }}08;">
        <div class="card-title">🎯 Empfehlung</div>
        <div style="font-size:16px; font-weight:600; color:{{ $colorHex }}; line-height:1.4; margin-bottom:16px;">
            {{ $analysis->recommendation ?? '—' }}
        </div>
        @if($input)
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:8px;">
            <div style="background:rgba(255,255,255,0.04); padding:10px; border-radius:8px;">
                <div style="font-size:11px; color:#64748b;">Umsatz</div>
                <div style="font-size:15px; font-weight:600; color:#e2e8f0; margin-top:2px;">
                    {{ number_format($input->revenue_current / 1000, 0) }}k €
                </div>
            </div>
            <div style="background:rgba(255,255,255,0.04); padding:10px; border-radius:8px;">
                <div style="font-size:11px; color:#64748b;">Eigenkapital</div>
                <div style="font-size:15px; font-weight:600; color:#e2e8f0; margin-top:2px;">
                    {{ number_format($input->equity / 1000, 0) }}k €
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- KPI Table --}}
<div class="gmbh-show-kpi-grid">

    <div class="card">
        <div class="card-title">📊 KPI Übersicht</div>
        @if($analysis->kpiResults->isNotEmpty())
        <div class="gmbh-show-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>KPI</th>
                    <th>Wert</th>
                    <th>Gewicht</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis->kpiResults->unique('kpi_code') as $kpi)
                <tr>
                    <td style="font-weight:500; color:#c7d2fe;">{{ $kpi->kpi_name }}</td>
                    <td style="font-weight:600; color:#e2e8f0;">
                        {{ $kpi->value !== null ? number_format((float)$kpi->value, 2) : '—' }}
                        <span style="font-size:11px; color:#475569;"> {{ $kpi->unit }}</span>
                    </td>
                    <td style="color:#64748b;">{{ $kpi->weight }}%</td>
                    <td>
                        <span class="badge badge-{{ $kpi->traffic_light }}">
                            @if($kpi->traffic_light === 'green') 🟢 Gut
                            @elseif($kpi->traffic_light === 'yellow') 🟡 Mittel
                            @else 🔴 Kritisch
                            @endif
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
            <div style="color:#475569; font-size:13px;">Keine KPIs berechnet.</div>
        @endif
    </div>

    {{-- Input Summary --}}
    @if($input)
    <div class="card">
        <div class="card-title">📋 Eingabewerte</div>
        <div class="gmbh-show-table-wrap">
        <table class="data-table">
            <tbody>
                @foreach([
                    ['Umsatz aktuell', number_format($input->revenue_current ?? 0, 0, ',', '.') . ' €'],
                    ['Umsatz Vorjahr', number_format($input->revenue_prev ?? 0, 0, ',', '.') . ' €'],
                    ['EBITDA', number_format($input->ebitda ?? 0, 0, ',', '.') . ' €'],
                    ['Jahresüberschuss', number_format($input->net_profit ?? 0, 0, ',', '.') . ' €'],
                    ['Eigenkapital', number_format($input->equity ?? 0, 0, ',', '.') . ' €'],
                    ['Gesamtverbindlichkeiten', number_format($input->total_debt ?? 0, 0, ',', '.') . ' €'],
                    ['Umlaufvermögen', number_format($input->current_assets ?? 0, 0, ',', '.') . ' €'],
                    ['Liquidität (Cash)', number_format($input->cash ?? 0, 0, ',', '.') . ' €'],
                    ['CAC', number_format($input->cac ?? 0, 0, ',', '.') . ' €'],
                    ['LTV', number_format($input->ltv ?? 0, 0, ',', '.') . ' €'],
                    ['Management-Score', ($input->mgmt_score ?? '—') . '/10'],
                    ['Markt-Score', ($input->market_score ?? '—') . '/10'],
                ] as [$label, $value])
                <tr>
                    <td style="color:#64748b; width:55%;">{{ $label }}</td>
                    <td style="font-weight:500;">{{ $value }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

</div>

{{-- Chart --}}
@if($analysis->kpiResults->isNotEmpty())
<div class="card" style="margin-bottom:20px;">
    <div class="card-title">📉 KPI Score-Visualisierung</div>
    <div id="kpiChart"></div>
</div>

@push('scripts')
<script>
const kpis = @json($analysis->kpiResults->unique('kpi_code')->values());
const labels = kpis.map(k => k.kpi_name);
const values = kpis.map(k => parseFloat(k.value) || 0);
const colors = kpis.map(k => k.traffic_light === 'green' ? '#10b981' : k.traffic_light === 'yellow' ? '#f59e0b' : '#ef4444');

new ApexCharts(document.querySelector("#kpiChart"), {
    chart: { type: 'bar', height: 220, background: 'transparent', toolbar: { show: false } },
    series: [{ name: 'Wert', data: values }],
    xaxis: { categories: labels, labels: { style: { colors: '#94a3b8', fontSize: '11px' } } },
    yaxis: { labels: { style: { colors: '#94a3b8' } } },
    colors: colors,
    plotOptions: { bar: { borderRadius: 4, distributed: true } },
    legend: { show: false },
    grid: { borderColor: 'rgba(99,102,241,0.1)' },
    theme: { mode: 'dark' },
    dataLabels: { style: { fontSize: '11px' } },
}).render();
</script>
@endpush
@endif

@include('partials.kpi-glossary')

{{-- Delete form --}}
<div style="text-align:right; margin-top:8px;">
    <form method="POST" action="{{ route('gmbh.destroy', $analysis) }}"
          onsubmit="return confirm('Analyse wirklich löschen?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">🗑 Löschen</button>
    </form>
</div>

@endsection
