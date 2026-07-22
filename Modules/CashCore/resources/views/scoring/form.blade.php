@extends('layouts.shell')

@section('title', __('Score Expense'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $transaction->description }} — {{ number_format($transaction->amount, 2) }}</h1>
        <form method="POST" action="{{ route('cashcore.scoring.store', $transaction) }}" class="mt-6 space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Purpose') }}</label><input type="text" name="purpose" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Benefit') }}</label><input type="text" name="benefit" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-4">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Revenue (0-10)') }}</label><input type="number" name="revenue_score" min="0" max="10" value="5" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Efficiency (0-10)') }}</label><input type="number" name="efficiency_score" min="0" max="10" value="5" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Strategic (0-10)') }}</label><input type="number" name="strategic_score" min="0" max="10" value="5" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Usage (0-10)') }}</label><input type="number" name="usage_score" min="0" max="10" value="5" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Score') }}</button>
        </form>
    </div>
@endsection
