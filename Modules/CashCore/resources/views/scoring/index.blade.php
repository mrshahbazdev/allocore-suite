@extends('layouts.shell')

@section('title', __('Expense Scoring'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Expense Scoring') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Unscored Expenses') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($unscoredExpenses as $expense)
                    <li class="flex justify-between"><span>{{ $expense->description }} — {{ number_format($expense->amount, 2) }}</span><a href="{{ route('cashcore.scoring.score', $expense) }}" class="text-indigo-600">{{ __('Score') }}</a></li>
                @empty
                    <li class="text-slate-500">{{ __('All expenses scored.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Scored Expenses') }}</h2>
            <table class="mt-3 min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Description') }}</th><th class="pb-2 pr-4">{{ __('Total') }}</th><th class="pb-2 pr-4">{{ __('Recommendation') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($scoredExpenses as $expense)
                        <tr>
                            <td class="py-2 pr-4">{{ $expense->description }}</td>
                            <td class="py-2 pr-4">{{ $expense->expenseScore->total_score }}/40</td>
                            <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $expense->expenseScore->recommendation === 'keep' ? 'bg-emerald-100 text-emerald-700' : ($expense->expenseScore->recommendation === 'reduce' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">{{ __($expense->expenseScore->recommendation) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
