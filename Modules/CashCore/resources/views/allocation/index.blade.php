@extends('layouts.shell')

@section('title', __('Profit Allocation'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Profit Allocation') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 text-sm text-slate-600">{{ __('Period') }}: {{ $period }} — {{ __('Total Revenue') }}: <span class="font-bold">{{ number_format($totalRevenue, 2) }}</span></div>
            <form method="POST" action="{{ route('cashcore.allocation.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                @foreach ($allocations as $allocation)
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="font-medium capitalize">{{ $allocation->bucket }}</div>
                        <div><input type="number" step="0.01" name="allocations[{{ $loop->index }}][percentage]" value="{{ $allocation->percentage }}" class="w-full rounded-lg border-slate-300" required></div>
                        <div class="text-right font-mono">{{ number_format($allocation->allocated_amount, 2) }}</div>
                        <input type="hidden" name="allocations[{{ $loop->index }}][bucket]" value="{{ $allocation->bucket }}">
                    </div>
                @endforeach
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Allocations') }}</button>
            </form>
        </div>
    </div>
@endsection
