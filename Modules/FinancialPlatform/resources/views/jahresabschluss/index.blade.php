@extends('layouts.shell')
@section('title', 'Jahresabschluss — Allocore')
@section('page-title', '📈 Jahresabschluss-Analysen')
@section('topbar-actions')
    <a href="{{ route('jahresabschluss.create') }}" class="btn btn-primary btn-sm">+ Neue Analyse</a>
@endsection
@push('styles')
<style>
    .ja-index-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .ja-index-actions { display: flex; gap: 6px; }
    @media (max-width: 640px) {
        .ja-index-actions { min-width: 210px; }
    }
</style>
@endpush
@section('content')
<div class="card">
    <div class="ja-index-table-wrap">
    <table class="data-table">
        <thead><tr><th>Analyse</th><th>Unternehmen</th><th>Status</th><th>Erstellt</th><th></th></tr></thead>
        <tbody>
            @forelse($analyses as $a)
            <tr>
                <td style="font-weight:500; color:#c7d2fe;">{{ $a->name }}</td>
                <td style="color:#94a3b8;">{{ $a->company->name ?? '—' }}</td>
                <td><span class="badge badge-{{ $a->status==='complete'?'green':'gray' }}">{{ $a->status }}</span></td>
                <td style="font-size:12px; color:#475569;">{{ $a->created_at->format('d.m.Y') }}</td>
                <td>
                    <div class="ja-index-actions">
                        <a href="{{ route('jahresabschluss.show', $a) }}" class="btn btn-secondary btn-sm">Ansehen</a>
                        <a href="{{ route('jahresabschluss.pdf', $a) }}" class="btn btn-secondary btn-sm">PDF</a>
                        <form method="POST" action="{{ route('jahresabschluss.destroy', $a) }}" onsubmit="return confirm('Löschen?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#475569; padding:40px;">
                Noch keine Analysen. <a href="{{ route('jahresabschluss.create') }}" style="color:#6366f1;">Jetzt erstellen →</a>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="pagination">{{ $analyses->links() }}</div>
</div>
@endsection
