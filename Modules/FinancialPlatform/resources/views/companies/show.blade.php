@extends('layouts.shell')
@section('title', $company->name . ' — Allocore')
@section('page-title', '🏢 ' . $company->name)
@section('topbar-actions')
    <a href="{{ route('companies.edit', $company) }}" class="btn btn-secondary btn-sm">✏ Bearbeiten</a>
    <a href="{{ route('companies.index') }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection
@push('styles')
<style>
    .company-show-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 20px;
    }
    .company-info-row {
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        display: flex;
        gap: 8px;
    }
    .company-analyses-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        gap: 10px;
    }
    .company-analyses-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 900px) {
        .company-show-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .company-info-row {
            flex-direction: column;
            gap: 2px;
        }
        .company-analyses-head {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush
@section('content')
<div class="company-show-grid">
    <div class="card" style="align-self:start;">
        <div class="card-title">Informationen</div>
        @foreach([
            ['Branche', $company->industry ?? '—'],
            ['Währung', $company->currency ?? 'EUR'],
            ['Land', $company->country ?? '—'],
            ['Erstellt', $company->created_at->format('d.m.Y')],
        ] as [$k,$v])
        <div class="company-info-row">
            <div style="font-size:12px; color:#64748b; width:90px;">{{ $k }}</div>
            <div style="font-size:13px; color:#cbd5e1;">{{ $v }}</div>
        </div>
        @endforeach
        @if($company->description)
        <div style="margin-top:12px; font-size:12px; color:#64748b; line-height:1.6;">{{ $company->description }}</div>
        @endif
    </div>
    <div class="card">
        <div class="company-analyses-head">
            <div class="card-title" style="margin-bottom:0;">Analysen ({{ $analyses->count() }})</div>
            <a href="{{ route('gmbh.create') }}" class="btn btn-primary btn-sm">+ Neue Analyse</a>
        </div>
        @if($analyses->isEmpty())
            <div style="text-align:center; padding:30px; color:#475569;">Noch keine Analysen für dieses Unternehmen.</div>
        @else
        <div class="company-analyses-table-wrap">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Typ</th><th>Score</th><th>Datum</th><th></th></tr></thead>
            <tbody>
            @foreach($analyses as $a)
            <tr>
                <td style="font-weight:500; color:#c7d2fe;">{{ $a->name }}</td>
                <td><span style="font-size:11px; color:#818cf8;">{{ $a->typeLabel() }}</span></td>
                <td>
                    @if($a->total_score !== null)
                        <span class="score-{{ $a->scoreColor() }}" style="font-weight:700;">{{ number_format($a->total_score,1) }}</span>
                    @else <span style="color:#475569;">—</span> @endif
                </td>
                <td style="font-size:12px; color:#475569;">{{ $a->created_at->format('d.m.Y') }}</td>
                <td><a href="{{ route($a->type.'.show', $a) }}" class="btn btn-secondary btn-sm">→</a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>
@endsection
