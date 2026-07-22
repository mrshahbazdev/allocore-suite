@extends('layouts.shell', ['title' => __('Organisation Check')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Organisation Check') }}</h1>

    @if (! $team)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-800">{{ __('Select a team first.') }}</div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Week') }} {{ $now->weekOfYear }} — {{ $team->name }}</h2>
            <form method="POST" action="{{ route('focusmatrix.org-check.store') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="flex items-center gap-2"><input type="checkbox" name="decides_what_clear" value="1" {{ $current?->decides_what_clear ? 'checked' : '' }} class="rounded border-slate-300"> {{ __('It is clear who decides what.') }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="responsibilities_clear" value="1" {{ $current?->responsibilities_clear ? 'checked' : '' }} class="rounded border-slate-300"> {{ __('Responsibilities are clear.') }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="reports_short" value="1" {{ $current?->reports_short ? 'checked' : '' }} class="rounded border-slate-300"> {{ __('Reports are short and decision-oriented.') }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="teams_small" value="1" {{ $current?->teams_small ? 'checked' : '' }} class="rounded border-slate-300"> {{ __('Teams are small enough.') }}</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $current?->notes }}</textarea>
                </div>
                @if ($current)
                    <div class="text-sm text-slate-500">{{ __('Health score') }}: <strong>{{ $current->health_score }}%</strong></div>
                @endif
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Organisation Check') }}</button>
            </form>
        </div>

        @if ($history->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('History') }}</h2>
                <ul class="divide-y divide-slate-100">
                    @foreach ($history as $check)
                        <li class="py-2 flex justify-between text-sm">
                            <span>{{ $check->year }}-W{{ str_pad($check->week, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="font-medium">{{ $check->health_score }}%</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</div>
@endsection
