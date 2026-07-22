@extends('layouts.shell')

@section('title', __('Quality Control'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Quality Control') }}</h1>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Open Rework') }}</div><div class="text-2xl font-bold {{ $open > 0 ? 'text-rose-600' : '' }}">{{ $open }}</div></div>
            @foreach (['material_defect','technique_error','equipment_issue','design_error','other'] as $c)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs uppercase text-slate-500">{{ ucfirst(str_replace('_', ' ', $c)) }}</div>
                    <div class="text-2xl font-bold">{{ $byCause[$c] ?? 0 }}</div>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Rework') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($recent as $event)
                    <div class="rounded-lg border border-slate-200 p-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium"><a href="{{ route('dentaltrack.admin.orders.show', $event->order) }}" class="text-indigo-600 hover:underline">#{{ $event->order?->id }}</a> - {{ $event->orderStep?->step_name }}</span>
                            <span class="text-xs font-semibold {{ $event->status->value === 'resolved' ? 'text-emerald-600' : 'text-rose-600' }}">{{ ucfirst(str_replace('_', ' ', $event->status->value)) }}</span>
                        </div>
                        <div class="text-sm text-slate-500">{{ ucfirst(str_replace('_', ' ', $event->cause->value)) }} - {{ $event->description }}</div>
                        <div class="text-xs text-slate-400">{{ __('Flagged by') }}: {{ $event->flaggedByUser?->name }}</div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('No rework events.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
