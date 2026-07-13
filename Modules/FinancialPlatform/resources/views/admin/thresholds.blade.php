@extends('layouts.admin')
@section('title', 'KPI-Schwellwerte — Allocore Admin')
@section('page-title', '⚙ KPI-Schwellwerte konfigurieren')

@section('content')

<div style="font-size:12px; color:#64748b; margin-bottom:20px; line-height:1.6; background:rgba(220,38,38,0.05); border:1px solid rgba(220,38,38,0.15); padding:12px 16px; border-radius:8px;">
    ⚠ Schwellwerte steuern das Traffic-Light-System. Änderungen wirken sich auf <strong>neue</strong> Berechnungen aus.
    Bestehende KPI-Ergebnisse müssen neu berechnet werden.
</div>

@foreach($grouped as $tool => $thresholds)
<div class="card" style="margin-bottom:16px;">
    <div class="card-title" style="margin-bottom:18px;">
        @php $icons = ['gmbh'=>'📊','jahresabschluss'=>'📈','immobilien'=>'🏘']; @endphp
        {{ $icons[$tool] ?? '📋' }} {{ ucfirst($tool) }} KPIs ({{ $thresholds->count() }})
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:140px;">KPI</th>
                <th>Einheit</th>
                <th>Grün ab/bis</th>
                <th>Gelb ab/bis</th>
                <th>Gewicht</th>
                <th>Lower=Better</th>
                <th>Aktiv</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($thresholds as $t)
        <tr>
            <td>
                <div style="font-weight:500; color:#e2e8f0; font-size:11px;">{{ $t->kpi_name }}</div>
                <div style="font-size:10px; color:#475569;">{{ $t->kpi_code }}</div>
            </td>
            <td style="color:#64748b; font-size:11px;">{{ $t->unit }}</td>
            <td>
                <form method="POST" action="{{ route('admin.thresholds.update', $t) }}" id="form-{{ $t->id }}">
                @csrf @method('PATCH')
                @if($t->lower_is_better)
                    <input type="number" step="0.01" name="green_max" class="form-control" style="width:80px;" value="{{ $t->green_max }}" title="Grün MAX">
                @else
                    <input type="number" step="0.01" name="green_min" class="form-control" style="width:80px;" value="{{ $t->green_min }}" title="Grün MIN">
                @endif
            </td>
            <td>
                @if($t->lower_is_better)
                    <input type="number" step="0.01" name="yellow_max" class="form-control" style="width:80px;" value="{{ $t->yellow_max }}" title="Gelb MAX">
                @else
                    <input type="number" step="0.01" name="yellow_min" class="form-control" style="width:80px;" value="{{ $t->yellow_min }}" title="Gelb MIN">
                @endif
            </td>
            <td>
                <input type="number" step="1" name="weight" class="form-control" style="width:60px;" value="{{ $t->weight ?? 0 }}">
            </td>
            <td style="text-align:center;">
                <input type="checkbox" name="lower_is_better" value="1" {{ $t->lower_is_better ? 'checked' : '' }}
                    style="width:16px; height:16px; accent-color:#dc2626;">
            </td>
            <td style="text-align:center;">
                <input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }}
                    style="width:16px; height:16px; accent-color:#34d399;">
            </td>
            <td>
                <button type="submit" class="btn btn-primary btn-sm">✓</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endforeach

@endsection
