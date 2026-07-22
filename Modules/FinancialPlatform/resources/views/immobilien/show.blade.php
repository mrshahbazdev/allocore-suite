@extends('layouts.shell')
@section('title', 'Immobilienanalyse — ' . $analysis->name)
@section('page-title', '🏘 ' . $analysis->name)
@section('topbar-actions')
    <a href="{{ route('immobilien.pdf', $analysis) }}" class="btn btn-secondary btn-sm">⬇ PDF</a>
    <a href="{{ route('immobilien.index') }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection
@push('styles')
<style>
    .immobilien-score-grid {
        display: grid;
        grid-template-columns: 200px 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    .immobilien-kpi-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .immobilien-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 1100px) {
        .immobilien-score-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 900px) {
        .immobilien-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
@section('content')

@php
    $score = $analysis->total_score ?? 0;
    $color = $analysis->scoreColor();
    $colorHex = ['green'=>'#10b981','yellow'=>'#f59e0b','red'=>'#ef4444','gray'=>'#64748b'][$color];
    $input = $analysis->immobilienInput;
@endphp

<div class="immobilien-score-grid">
    {{-- Score --}}
    <div class="card" style="text-align:center; padding:28px 16px; border-color:{{ $colorHex }}40;">
        <div style="font-size:11px; color:#64748b; text-transform:uppercase; margin-bottom:10px;">Score</div>
        <div class="score-lg score-{{ $color }}">{{ number_format($score, 1) }}</div>
        <div style="font-size:13px; color:#475569; margin-top:4px;">/100</div>
        <div style="margin-top:12px;"><span class="badge badge-{{ $color }}">
            @if($color==='green') ✅ Empfohlen @elseif($color==='yellow') ⚠ Prüfen @else ❌ Nicht empfohlen @endif
        </span></div>
        <div style="margin-top:14px; height:6px; background:rgba(255,255,255,0.08); border-radius:3px; overflow:hidden;">
            <div style="height:100%; width:{{ $score }}%; background:{{ $colorHex }};"></div>
        </div>
    </div>

    {{-- Empfehlung --}}
    <div class="card" style="border-color:{{ $colorHex }}30;">
        <div class="card-title">🎯 Empfehlung</div>
        <div style="font-size:15px; font-weight:600; color:{{ $colorHex }}; margin-bottom:16px; line-height:1.4;">
            {{ $analysis->recommendation }}
        </div>
        @if($input)
        <div class="form-grid">
            @foreach([
                ['Kaufpreis', number_format($input->purchase_price, 0, ',', '.') . ' €'],
                ['Eigenkapital', number_format($input->equity, 0, ',', '.') . ' €'],
                ['Darlehen', number_format($derived['darlehen'], 0, ',', '.') . ' €'],
                ['Gesamtinvest.', number_format($derived['gesamtinvestition'], 0, ',', '.') . ' €'],
            ] as [$l,$v])
            <div style="background:rgba(255,255,255,0.04); padding:10px; border-radius:8px;">
                <div style="font-size:11px; color:#64748b;">{{ $l }}</div>
                <div style="font-size:14px; font-weight:600; color:#e2e8f0; margin-top:2px;">{{ $v }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Cashflow Summary --}}
    <div class="card">
        <div class="card-title">💰 Cashflow-Übersicht (p.a.)</div>
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach([
                ['Bruttoertrag', $derived['noi'] + ($input->management_costs_pct/100 * $derived['noi']), '#94a3b8'],
                ['NOI (Nettobetriebsertrag)', $derived['noi'], '#818cf8'],
                ['Schuldendienst', -$derived['schuldendienst'], '#ef4444'],
                ['Cashflow', $derived['cashflow'], $derived['cashflow'] >= 0 ? '#10b981' : '#ef4444'],
            ] as [$label, $val, $c])
            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                <span style="font-size:13px; color:#94a3b8;">{{ $label }}</span>
                <span style="font-size:15px; font-weight:600; color:{{ $c }};">
                    {{ ($val >= 0 ? '+' : '') . number_format($val, 0, ',', '.') }} €
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- KPI Table --}}
<div class="immobilien-kpi-grid">
<div class="card">
    <div class="card-title">📊 KPI-Ergebnisse</div>
    <div class="immobilien-table-wrap">
    <table class="data-table">
        <thead><tr><th>KPI</th><th>Wert</th><th>Gewicht</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($analysis->kpiResults->unique('kpi_code') as $kpi)
            <tr>
                <td style="font-weight:500; color:#c7d2fe;">{{ $kpi->kpi_name }}</td>
                <td style="font-weight:600;">
                    {{ number_format((float)$kpi->value, 2) }}
                    <span style="font-size:11px; color:#475569;">{{ $kpi->unit }}</span>
                </td>
                <td style="color:#64748b;">{{ $kpi->weight ?? 0 }}%</td>
                <td><span class="badge badge-{{ $kpi->traffic_light }}">
                    {{ $kpi->traffic_light==='green'?'🟢 Gut':($kpi->traffic_light==='yellow'?'🟡 Mittel':'🔴 Kritisch') }}
                </span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

@if($input)
<div class="card">
    <div class="card-title">📋 Objektdaten</div>
    <div class="immobilien-table-wrap">
    <table class="data-table">
        <tbody>
            @foreach([
                ['Objekttyp', $input->property_type ?? '—'],
                ['Lage', $input->location ?? '—'],
                ['Wohnfläche', ($input->area_sqm ?? '—') . ' m²'],
                ['Nettokaltmiete', number_format($input->rent_net, 0, ',', '.') . ' € / Monat'],
                ['Marktmiete', number_format($input->market_rent ?? 0, 0, ',', '.') . ' € / Monat'],
                ['Leerstand', $input->vacancy_rate . ' %'],
                ['Zinssatz', $input->loan_rate . ' %'],
                ['Tilgung', $input->repayment_rate . ' %'],
                ['Lage-Score', ($input->location_score ?? '—') . '/10'],
                ['Zustand-Score', ($input->condition_score ?? '—') . '/10'],
            ] as [$l,$v])
            <tr>
                <td style="color:#64748b; font-size:12px; width:50%;">{{ $l }}</td>
                <td style="font-weight:500;">{{ $v }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif
</div>

@include('financialplatform::partials.kpi-glossary')

<div style="text-align:right; margin-top:12px;">
    <form method="POST" action="{{ route('immobilien.destroy', $analysis) }}" onsubmit="return confirm('Löschen?')">
        @csrf @method('DELETE')
        <button class="btn btn-danger btn-sm">🗑 Analyse löschen</button>
    </form>
</div>

@endsection
