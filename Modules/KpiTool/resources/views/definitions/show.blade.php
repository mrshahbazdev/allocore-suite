@extends('layouts.shell')

@section('title', $definition->name)
@section('page-title', $definition->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $definition->name }}</h1>
                <p class="text-sm text-slate-500">{{ $definition->description }}</p>
                <div class="mt-2 text-xs text-slate-500">{{ $definition->formula }} — {{ $definition->frequency }} — {{ __($definition->direction) }}</div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('kpitool.values.index', $definition) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Values') }}</a>
                <a href="{{ route('kpitool.targets.show', $definition) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Targets') }}</a>
                <a href="{{ route('kpitool.definitions.edit', $definition) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('kpitool.definitions.destroy', $definition) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Values') }}</h2>
                <table class="mt-4 min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Value') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($values as $value)
                            <tr>
                                <td class="py-2 pr-4">{{ $value->recorded_at->format('Y-m-d') }}</td>
                                <td class="py-2 pr-4">{{ $value->value }} {{ $definition->unit }}</td>
                                <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $value->status === 'critical' ? 'bg-rose-100 text-rose-700' : ($value->status === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">{{ __($value->status) }}</span></td>
                                <td class="py-2"><a href="{{ route('kpitool.values.edit', $value) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Value') }}</h2>
                <form method="POST" action="{{ route('kpitool.values.store', $definition) }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="block text-sm font-medium text-slate-700">{{ __('Value') }}</label><input type="number" step="any" name="value" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                        <div><label class="block text-sm font-medium text-slate-700">{{ __('Date') }}</label><input type="date" name="recorded_at" value="{{ now()->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                    </div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label><textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea></div>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Value') }}</button>
                </form>
            </div>
        </div>

        @if ($values->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Trend') }}</h2>
                @php
                    $max = $values->max('value') ?: 1;
                    $min = $values->min('value') ?: 0;
                    $range = max($max - $min, 1);
                    $width = 600;
                    $height = 200;
                    $count = $values->count();
                    $points = $values->map(function ($value, $index) use ($min, $range, $width, $height, $count) {
                        $x = $count > 1 ? ($index / ($count - 1)) * $width : $width / 2;
                        $y = $height - (($value->value - $min) / $range) * $height;
                        return round($x, 2).','.round($y, 2);
                    })->implode(' ');
                @endphp
                <svg viewBox="0 0 {{ $width }} {{ $height }}" class="mt-4 h-48 w-full overflow-visible">
                    <polyline fill="none" stroke="#4f46e5" stroke-width="2" points="{{ $points }}" />
                    @foreach ($values as $value)
                        @php
                            $x = $count > 1 ? ($loop->index / ($count - 1)) * $width : $width / 2;
                            $y = $height - (($value->value - $min) / $range) * $height;
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="{{ $value->status === 'critical' ? '#f43f5e' : ($value->status === 'warning' ? '#f59e0b' : '#10b981') }}" />
                    @endforeach
                </svg>
            </div>
        @endif
    </div>
@endsection
