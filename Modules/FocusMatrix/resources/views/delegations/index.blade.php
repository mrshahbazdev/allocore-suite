@extends('layouts.shell', ['title' => __('Delegations')])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Delegations') }}</h1>
        <a href="{{ route('focusmatrix.delegations.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Delegation') }}</a>
    </div>

    @if ($delegations->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No delegations yet.') }}</div>
    @else
        <div class="space-y-3">
            @foreach ($delegations as $delegation)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-slate-900">{{ $delegation->task?->title }}</div>
                        <div class="text-sm text-slate-500">{{ $delegation->delegateUser?->name ?? $delegation->delegate_name_fallback }} — {{ $delegation->status }}</div>
                    </div>
                    <a href="{{ route('focusmatrix.delegations.show', $delegation) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
