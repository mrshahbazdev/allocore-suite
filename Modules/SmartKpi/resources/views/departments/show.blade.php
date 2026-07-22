@extends('layouts.shell')

@section('title', $department->name)

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $department->name }}</h1>
                <p class="text-sm text-slate-500">{{ $department->company->name }}</p>
            </div>
            <a href="{{ route('smartkpi.departments.employees.create', $department) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Employee') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('KPIs') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($department->kpiDefinitions as $kpi)
                    <li><a href="{{ route('smartkpi.kpi-definitions.show', $kpi) }}" class="text-indigo-600">{{ $kpi->localizedName() }}</a></li>
                @empty
                    <li class="text-slate-500">{{ __('No KPIs yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
