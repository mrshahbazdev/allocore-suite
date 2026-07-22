@extends('layouts.shell', ['title' => __('Assigned to me')])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Assigned to me') }}</h1>

    @if ($delegations->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('Nothing assigned yet.') }}</div>
    @else
        <div class="space-y-3">
            @foreach ($delegations as $delegation)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-slate-900">{{ $delegation->task?->title }}</div>
                        <div class="text-sm text-slate-500">{{ __('From') }} {{ $delegation->delegator?->name }}</div>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('focusmatrix.delegations.accept', $delegation) }}">
                            @csrf
                            <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Accept') }}</button>
                        </form>
                        <form method="POST" action="{{ route('focusmatrix.delegations.decline', $delegation) }}">
                            @csrf
                            <button class="rounded-lg bg-rose-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Decline') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
