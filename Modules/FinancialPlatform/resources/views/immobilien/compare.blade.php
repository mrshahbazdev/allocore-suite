@extends('layouts.shell')

@section('title', 'Immobilien-Vergleich — Allocore')
@section('page-title', 'Immobilien-Vergleich')

@section('topbar-actions')
    <a href="{{ route('immobilien.index') }}" class="btn btn-secondary btn-sm">{{ __('← Zurück') }}</a>
@endsection

@section('content')
<div class="card">
    <div class="card-title">{{ __('Ausgewählte Immobilienanalysen') }}</div>

    @if($analyses->isEmpty())
        <div style="text-align:center; padding:40px; color:#475569;">
            {{ __('Keine Analysen zur Vergleich ausgewählt.') }}
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Unternehmen') }}</th>
                        <th>{{ __('Kaufpreis') }}</th>
                        <th>{{ __('Eigenkapital') }}</th>
                        <th>{{ __('Nettomiete') }}</th>
                        <th>{{ __('Score') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analyses as $analysis)
                        <tr>
                            <td>{{ $analysis->name }}</td>
                            <td>{{ $analysis->company?->name ?? '—' }}</td>
                            <td>{{ number_format($analysis->immobilienInput?->purchase_price ?? 0, 0, ',', '.') }} €</td>
                            <td>{{ number_format($analysis->immobilienInput?->equity ?? 0, 0, ',', '.') }} €</td>
                            <td>{{ number_format($analysis->immobilienInput?->rent_net ?? 0, 0, ',', '.') }} €</td>
                            <td>{{ number_format($analysis->total_score ?? 0, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
