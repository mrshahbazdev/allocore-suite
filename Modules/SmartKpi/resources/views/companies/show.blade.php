@extends('layouts.shell')

@section('title', $company->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $company->name }}</h1>
                <p class="text-sm text-slate-500">{{ $company->description }}</p>
                <div class="mt-2 text-xs text-slate-500">{{ $company->industry }} — {{ $company->size }}</div>
            </div>
            <a href="{{ route('smartkpi.companies.departments.create', $company) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Department') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Departments') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($company->departments as $department)
                    <li><a href="{{ route('smartkpi.departments.show', $department) }}" class="text-indigo-600">{{ $department->name }}</a></li>
                @empty
                    <li class="text-slate-500">{{ __('No departments yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
