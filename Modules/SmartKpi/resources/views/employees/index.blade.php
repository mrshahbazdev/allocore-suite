@extends('layouts.shell')

@section('title', __('Employees'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Employees') }}</h1>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Department') }}</th><th class="pb-2 pr-4">{{ __('Role') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($employees as $employee)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $employee->name }}</td>
                            <td class="py-2 pr-4">{{ $employee->department?->name }}</td>
                            <td class="py-2 pr-4">{{ $employee->role }}</td>
                            <td class="py-2"><a href="{{ route('smartkpi.employees.edit', $employee) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $employees->links() }}</div>
        </div>
    </div>
@endsection
