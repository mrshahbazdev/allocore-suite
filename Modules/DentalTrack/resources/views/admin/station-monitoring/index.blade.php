@extends('layouts.shell')

@section('title', __('Station Monitoring'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Station Monitoring') }}</h1>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($statuses as $ws)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="font-semibold text-slate-900">{{ $ws['name'] }}</div>
                    <div class="text-sm text-slate-500">{{ __('Active Orders') }}: {{ $ws['active_orders'] }}</div>
                    <div class="mt-2 text-xs font-semibold {{ $ws['idle'] ? 'text-amber-600' : 'text-emerald-600' }}">{{ $ws['idle'] ? __('Idle') : __('Working') }}</div>
                </div>
            @empty
                <div class="text-sm text-slate-500">{{ __('No active workstations.') }}</div>
            @endforelse
        </div>
    </div>
@endsection
