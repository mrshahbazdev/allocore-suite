@extends('layouts.shell')

@section('title', __('Departments'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Departments') }}</h1>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Company') }}</th><th class="pb-2 pr-4">{{ __('KPIs') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($departments as $department)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $department->name }}</td>
                            <td class="py-2 pr-4">{{ $department->company->name }}</td>
                            <td class="py-2 pr-4">{{ $department->kpiDefinitions->count() }}</td>
                            <td class="py-2"><a href="{{ route('smartkpi.departments.show', $department) }}" class="text-indigo-600">{{ __('View') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $departments->links() }}</div>
        </div>
    </div>
@endsection
