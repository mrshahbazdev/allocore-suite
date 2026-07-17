@extends('layouts.guest')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-16">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Status Page') }}</h1>
            @php($statusBadge = match($overallStatus) { 'operational' => 'bg-emerald-100 text-emerald-700', 'degraded' => 'bg-amber-100 text-amber-700', default => 'bg-rose-100 text-rose-700' })
            <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $statusBadge }}">{{ ucfirst($overallStatus) }}</span>
        </div>

        @if ($active->isNotEmpty())
            <div class="mt-8 space-y-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Active incidents') }}</h2>
                @foreach ($active as $incident)
                    @php($badge = match($incident->severity) { 'critical' => 'bg-rose-100 text-rose-700', 'major' => 'bg-orange-100 text-orange-700', 'minor' => 'bg-amber-100 text-amber-700', default => 'bg-slate-100 text-slate-700' })
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $incident->title }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $incident->description }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">{{ $incident->severity }}</span>
                        </div>
                        <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                            <span>{{ __('Status') }}: {{ $incident->status }}</span>
                            <span>{{ __('Started') }}: {{ $incident->started_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-8 rounded-xl border border-emerald-200 bg-emerald-50 p-6 text-emerald-800">
                {{ __('All systems operational. No active incidents.') }}
            </div>
        @endif

        @if ($resolved->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent resolved incidents') }}</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($resolved as $incident)
                        <li class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4">
                            <span class="font-medium text-slate-900">{{ $incident->title }}</span>
                            <span class="text-sm text-slate-500">{{ $incident->resolved_at?->diffForHumans() ?? '—' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
