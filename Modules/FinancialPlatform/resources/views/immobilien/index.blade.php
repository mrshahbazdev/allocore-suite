@extends('layouts.shell')
@section('title', 'Immobilienanalyse — Allocore')
@section('page-title', '🏘 Immobilien-Analysen')
@section('topbar-actions')
    <a href="{{ route('immobilien.create') }}" class="btn btn-primary btn-sm">+ Neue Analyse</a>
@endsection
@push('styles')
<style>
    .immobilien-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .immobilien-row-actions {
        display: flex;
        gap: 6px;
    }
    @media (max-width: 640px) {
        .immobilien-row-actions {
            min-width: 210px;
        }
    }
</style>
@endpush
@section('content')
<div class="card">
    <div class="immobilien-table-wrap">
    <table class="data-table">
        <thead><tr><th>Analyse</th><th>Unternehmen</th><th>Score</th><th>Empfehlung</th><th>Erstellt</th><th></th></tr></thead>
        <tbody>
            @forelse($analyses as $a)
            <tr>
                <td style="font-weight:500; color:#c7d2fe;">{{ $a->name }}</td>
                <td style="color:#94a3b8;">{{ $a->company->name ?? '—' }}</td>
                <td>
                    @if($a->total_score !== null)
                        <span class="score-{{ $a->scoreColor() }}" style="font-weight:700;">{{ number_format($a->total_score,1) }}</span>
                        <span style="color:#475569; font-size:11px;">/100</span>
                    @else <span style="color:#475569;">—</span> @endif
                </td>
                <td style="font-size:12px; color:#94a3b8;">{{ Str::limit($a->recommendation ?? '—', 45) }}</td>
                <td style="font-size:12px; color:#475569;">{{ $a->created_at->format('d.m.Y') }}</td>
                <td>
                    <div class="immobilien-row-actions">
                        <a href="{{ route('immobilien.show', $a) }}" class="btn btn-secondary btn-sm">Ansehen</a>
                        <a href="{{ route('immobilien.pdf', $a) }}" class="btn btn-secondary btn-sm">PDF</a>
                        <form method="POST" action="{{ route('immobilien.destroy', $a) }}" onsubmit="return confirm('Löschen?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; color:#475569; padding:40px;">
                Noch keine Analysen. <a href="{{ route('immobilien.create') }}" style="color:#6366f1;">Jetzt starten →</a>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="pagination">{{ $analyses->links() }}</div>
</div>
@endsection
