@extends('layouts.shell')

@section('title', __('Values for :name', ['name' => $kpiDefinition->name]))
@section('page-title', __('Values for :name', ['name' => $kpiDefinition->name]))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ $kpiDefinition->name }}</h1>
            <a href="{{ route('kpitool.definitions.show', $kpiDefinition) }}" class="text-indigo-600">{{ __('Back') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Value') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($values as $value)
                        <tr>
                            <td class="py-2 pr-4">{{ $value->recorded_at->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4">{{ $value->value }} {{ $kpiDefinition->unit }}</td>
                            <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $value->status === 'critical' ? 'bg-rose-100 text-rose-700' : ($value->status === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">{{ __($value->status) }}</span></td>
                            <td class="py-2"><a href="{{ route('kpitool.values.edit', $value) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $values->links() }}</div>
        </div>
    </div>
@endsection
