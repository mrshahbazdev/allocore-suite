@extends('layouts.shell')
@section('title', 'Jahresabschluss — ' . $analysis->name)
@section('page-title', '📈 ' . $analysis->name)
@section('topbar-actions')
    <a href="{{ route('jahresabschluss.pdf', $analysis) }}" class="btn btn-secondary btn-sm">⬇ PDF</a>
    <a href="{{ route('jahresabschluss.index') }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection
@push('styles')
<style>
    .ja-show-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .ja-show-year-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
    }
    @media (max-width: 640px) {
        .ja-show-year-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@section('content')

@php
    $years = $analysis->jahresabschlussInputs;
    $kpis  = $analysis->kpiResults;
    $kpiCodes = $kpis->pluck('kpi_code')->unique()->values();
@endphp

{{-- KPI Grid: Rows = KPIs, Cols = Years --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-title">📊 KPI-Übersicht (Mehrjahresvergleich)</div>
    @if($years->isEmpty())
        <p style="color:#475569;">Keine Jahreswerte vorhanden.</p>
    @else
    <div class="ja-show-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>KPI</th>
                @foreach($years as $y)
                    <th style="text-align:center;">{{ $y->year_label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($kpiCodes as $code)
            @php $kpiName = $kpis->where('kpi_code',$code)->first()?->kpi_name; @endphp
            <tr>
                <td style="font-weight:500; color:#c7d2fe; width:180px;">{{ $kpiName }}</td>
                @foreach($years as $y)
                @php $k = $kpis->where('kpi_code',$code)->where('year_label',$y->year_label)->first(); @endphp
                <td style="text-align:center;">
                    @if($k)
                    <div style="display:flex; flex-direction:column; align-items:center; gap:3px;">
                        <span class="tl-{{ $k->traffic_light }}" style="font-weight:600;">
                            {{ number_format((float)$k->value, 2) }} {{ $k->unit }}
                        </span>
                        <span style="font-size:14px;">
                            {{ $k->traffic_light==='green'?'🟢':($k->traffic_light==='yellow'?'🟡':'🔴') }}
                        </span>
                    </div>
                    @else <span style="color:#475569;">—</span> @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

{{-- Auto-generated Bericht --}}
@if($bericht)
<div class="card" style="margin-bottom:20px; border-color:rgba(99,102,241,0.25); background:rgba(99,102,241,0.05);">
    <div class="card-title">📝 Automatischer Bericht</div>
    <p style="font-size:14px; color:#cbd5e1; line-height:1.7;">{{ $bericht }}</p>
</div>
@endif

{{-- Year Data Tables --}}
<div class="ja-show-year-grid">
@foreach($years as $y)
<div class="card">
    <div class="card-title">📅 {{ $y->year_label }}</div>
    <table class="data-table">
        <tbody>
            @foreach([
                ['Umsatz', $y->revenue],
                ['EBIT', $y->ebit],
                ['Jahresüberschuss', $y->net_profit],
                ['Eigenkapital', $y->equity],
                ['Bilanzsumme', $y->total_assets],
                ['Umlaufvermögen', $y->current_assets],
                ['Kurzfr. Verbindlichkeiten', $y->current_liabilities],
                ['Zinsaufwand', $y->interest_exp],
            ] as [$label, $val])
            <tr>
                <td style="color:#64748b; font-size:12px;">{{ $label }}</td>
                <td style="font-weight:500; text-align:right;">
                    @if($val !== null) {{ number_format($val, 0, ',', '.') }} €
                    @else <span style="color:#475569;">—</span> @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach
</div>

@include('financialplatform::partials.kpi-glossary')

@endsection
