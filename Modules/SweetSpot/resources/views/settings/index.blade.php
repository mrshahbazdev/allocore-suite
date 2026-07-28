@extends('layouts.shell', ['title' => __('SweetSpot Scoring Weights')])

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('SweetSpot') }}</p>
        <h1 class="text-3xl font-bold text-slate-900">{{ __('Scoring weights') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Higher weight means the criterion has more impact on the total score. Save to recalculate all customer scores.') }}</p>
    </div>

    <form method="POST" action="{{ route('sweetspot.settings.update') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        @php($labels = ['profitability' => __('Profitability'), 'effort' => __('Low effort'), 'chemistry' => __('Chemistry'), 'growth' => __('Growth'), 'repeat' => __('Repeat rate'), 'recommendation' => __('Recommendations'), 'payment' => __('Payment willingness')])

        <div class="space-y-4">
            @foreach ($labels as $key => $label)
                <div class="flex items-center justify-between gap-4">
                    <label class="text-sm font-medium text-slate-700">{{ $label }}</label>
                    <input type="number" name="weights[{{ $key }}]" value="{{ old('weights.'.$key, $weights[$key]->weight ?? 1) }}" min="0" max="10" class="w-24 rounded-lg border-slate-300 text-sm text-right focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Save & recalculate') }}</button>
            <a href="{{ route('sweetspot.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
