@extends('layouts.shell')

@section('title', 'GmbH Analysen — Allocore')
@section('page-title', '📊 GmbH Analysen')

@section('topbar-actions')
    <a href="{{ route('gmbh.create') }}" class="btn btn-primary btn-sm">+ Neue Analyse</a>
@endsection

@push('styles')
<style>
    .gmbh-index-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .gmbh-index-actions { display: flex; gap: 6px; justify-content: flex-end; }
    @media (max-width: 640px) {
        .gmbh-index-actions { min-width: 210px; }
    }
</style>
@endpush

@section('content')

@if($analyses->isEmpty())
<div class="card" style="text-align:center; padding:60px 20px;">
    <div style="font-size:48px; margin-bottom:16px;">📊</div>
    <div style="font-size:18px; font-weight:600; color:#c7d2fe; margin-bottom:8px;">Noch keine GmbH-Analysen</div>
    <div style="font-size:13px; color:#475569; margin-bottom:24px;">Erstellen Sie Ihre erste GmbH-Analyse und erhalten Sie einen Finanz-Score von 0–100.</div>
    <a href="{{ route('gmbh.create') }}" class="btn btn-primary">📊 Erste Analyse erstellen</a>
</div>
@else
<div class="card">
    <div class="gmbh-index-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Analyse</th>
                <th>Unternehmen</th>
                <th>Score</th>
                <th>Empfehlung</th>
                <th>Erstellt</th>
                <th style="text-align:right;">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analyses as $a)
            <tr>
                <td>
                    <a href="{{ route('gmbh.show', $a) }}" style="font-weight:500; color:#c7d2fe; text-decoration:none;">
                        {{ $a->name }}
                    </a>
                </td>
                <td style="color:#94a3b8;">{{ $a->company->name ?? '—' }}</td>
                <td>
                    @if($a->total_score !== null)
                        @php $sc = $a->scoreColor(); @endphp
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="font-size:20px; font-weight:700;" class="score-{{ $sc }}">
                                {{ number_format($a->total_score, 1) }}
                            </div>
                            <div style="width:60px; height:6px; background:rgba(255,255,255,0.06); border-radius:3px; overflow:hidden;">
                                @php $hex = ['green'=>'#10b981','yellow'=>'#f59e0b','red'=>'#ef4444','gray'=>'#64748b'][$sc]; @endphp
                                <div style="height:100%; width:{{ $a->total_score }}%; background:{{ $hex }};"></div>
                            </div>
                        </div>
                    @else
                        <span style="color:#475569;">—</span>
                    @endif
                </td>
                <td>
                    @if($a->recommendation)
                        <span style="font-size:12px; color:#94a3b8;">{{ Str::limit($a->recommendation, 40) }}</span>
                    @else
                        <span style="color:#475569;">—</span>
                    @endif
                </td>
                <td style="font-size:12px; color:#475569;">{{ $a->created_at->format('d.m.Y') }}</td>
                <td style="text-align:right;">
                    <div class="gmbh-index-actions">
                        <a href="{{ route('gmbh.show', $a) }}" class="btn btn-secondary btn-sm">Ansehen</a>
                        <a href="{{ route('gmbh.pdf', $a) }}" class="btn btn-secondary btn-sm">PDF</a>
                        <form method="POST" action="{{ route('gmbh.destroy', $a) }}" onsubmit="return confirm('Löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div class="pagination">{{ $analyses->links() }}</div>
</div>
@endif

@endsection
