@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Alerts') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Get notified when important metrics cross your thresholds.') }}</p>
        </div>
        <a href="{{ route('alerts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create alert') }}</a>
    </div>

    @if ($alerts->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No alerts configured yet.') }}</div>
    @else
        <div class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white shadow-sm">
            @foreach ($alerts as $alert)
                <div class="flex items-center justify-between px-5 py-4">
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $alert->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $alert->metric }} {{ $alert->operator }} {{ $alert->threshold }}</p>
                        @if ($alert->last_value !== null)
                            <p class="text-xs text-slate-400">{{ __('Last value') }}: {{ number_format($alert->last_value, 2) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $alert->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $alert->is_active ? __('Active') : __('Inactive') }}</span>
                        <form method="POST" action="{{ route('alerts.test', $alert) }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-indigo-600 hover:underline">{{ __('Test') }}</button>
                        </form>
                        <a href="{{ route('alerts.edit', $alert) }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('alerts.destroy', $alert) }}" onsubmit="return confirm('{{ __('Delete this alert?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
