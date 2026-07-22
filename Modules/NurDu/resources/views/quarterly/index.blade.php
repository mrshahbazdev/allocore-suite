@extends('layouts.shell', ['title' => __('Quarterly Focus')])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    <h1 class="text-2xl font-bold text-slate-900">{{ __('Quarterly Focus') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('nurdu.quarterly.store') }}" class="grid md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Quarter') }}</label>
                <select name="quarter" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    <option value="Q1" {{ $currentQuarter === 'Q1' ? 'selected' : '' }}>Q1</option>
                    <option value="Q2" {{ $currentQuarter === 'Q2' ? 'selected' : '' }}>Q2</option>
                    <option value="Q3" {{ $currentQuarter === 'Q3' ? 'selected' : '' }}>Q3</option>
                    <option value="Q4" {{ $currentQuarter === 'Q4' ? 'selected' : '' }}>Q4</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Year') }}</label>
                <input type="number" name="year" value="{{ $currentYear }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                <input type="text" name="notes" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>

    @if ($focuses->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No quarterly focuses yet.') }}</div>
    @else
        <div class="grid md:grid-cols-2 gap-4">
            @foreach ($focuses as $focus)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold text-slate-900">{{ $focus->quarter }} {{ $focus->year }}</div>
                        <form method="POST" action="{{ route('nurdu.quarterly.destroy', $focus) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 hover:underline text-sm">{{ __('Delete') }}</button>
                        </form>
                    </div>
                    <p class="text-sm text-slate-600 mt-2">{{ $focus->notes }}</p>
                    <div class="mt-3 text-sm text-slate-700">{{ $focus->strategicPriorities->count() }} {{ __('priorities') }}</div>
                    <a href="{{ route('nurdu.quarterly.show', $focus) }}" class="mt-3 inline-block text-indigo-600 hover:underline text-sm">{{ __('Manage Priorities') }}</a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
