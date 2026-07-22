@extends('layouts.shell', ['title' => __('Quarterly Focus')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $quarterlyFocus->quarter }} {{ $quarterlyFocus->year }}</h1>
        <a href="{{ route('nurdu.quarterly.index') }}" class="text-indigo-600 hover:underline">{{ __('Back') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Priority') }}</h2>
        <form method="POST" action="{{ route('nurdu.quarterly.priorities.store', $quarterlyFocus) }}" class="mt-4 grid md:grid-cols-3 gap-4">
            @csrf
            <input type="text" name="title" placeholder="Title" class="rounded-lg border-slate-300 shadow-sm" required>
            <input type="text" name="owner" placeholder="Owner" class="rounded-lg border-slate-300 shadow-sm">
            <input type="text" name="kpi" placeholder="KPI" class="rounded-lg border-slate-300 shadow-sm">
            <div class="md:col-span-3 flex justify-end">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Priority') }}</button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @foreach ($quarterlyFocus->strategicPriorities as $priority)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <form method="POST" action="{{ route('nurdu.priorities.update', $priority) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <div class="grid md:grid-cols-2 gap-4">
                        <input type="text" name="title" value="{{ $priority->title }}" class="rounded-lg border-slate-300 shadow-sm" required>
                        <select name="status" class="rounded-lg border-slate-300 shadow-sm">
                            <option value="on_track" {{ $priority->status === 'on_track' ? 'selected' : '' }}>{{ __('On Track') }}</option>
                            <option value="at_risk" {{ $priority->status === 'at_risk' ? 'selected' : '' }}>{{ __('At Risk') }}</option>
                            <option value="off_track" {{ $priority->status === 'off_track' ? 'selected' : '' }}>{{ __('Off Track') }}</option>
                        </select>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <input type="text" name="owner" value="{{ $priority->owner }}" class="rounded-lg border-slate-300 shadow-sm">
                        <input type="text" name="kpi" value="{{ $priority->kpi }}" class="rounded-lg border-slate-300 shadow-sm">
                    </div>
                    <textarea name="notes" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm">{{ $priority->notes }}</textarea>
                    <div class="flex justify-between items-center">
                        <button class="text-indigo-600 hover:underline text-sm">{{ __('Update') }}</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('nurdu.priorities.destroy', $priority) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                    @csrf @method('DELETE')
                    <button class="text-rose-600 hover:underline text-sm">{{ __('Delete') }}</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
