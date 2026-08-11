@php
    $statusClass = match ($project?->status) {
        'completed' => 'bg-emerald-100 text-emerald-700',
        'failed' => 'bg-rose-100 text-rose-700',
        default => 'bg-amber-100 text-amber-700',
    };
@endphp

<div @if ($project?->isInProgress()) wire:poll.3s @endif>
    <p class="text-sm text-slate-500">
        {{ $project?->website }}
        · <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $project?->statusLabel() }}</span>
        · {{ $project?->progressPercent() }}%
    </p>

    @if ($project?->isInProgress())
        <div class="mt-3 h-2 w-full max-w-md overflow-hidden rounded-full bg-slate-100">
            <div class="h-2 rounded-full bg-indigo-600 transition-all" style="width: {{ $project->progressPercent() }}%"></div>
        </div>
    @endif

    @if ($project?->status === 'failed' && $project?->error)
        <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $project->error }}</div>
    @endif

    @if ($project?->isInProgress())
        <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
            {{ __('Your cluster is being generated. This page will update automatically.') }}
        </div>
    @endif
</div>
