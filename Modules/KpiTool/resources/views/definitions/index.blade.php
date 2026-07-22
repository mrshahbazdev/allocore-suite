@extends('layouts.shell')

@section('title', __('KPIs'))
@section('page-title', __('KPIs'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('KPI Definitions') }}</h1>
            <a href="{{ route('kpitool.definitions.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New KPI') }}</a>
        </div>

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-wrap items-end gap-3">
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Search') }}</label><input type="text" name="search" value="{{ request('search') }}" class="mt-1 rounded-lg border-slate-300"></div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
                <select name="category" class="mt-1 rounded-lg border-slate-300">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Category') }}</th><th class="pb-2 pr-4">{{ __('Unit') }}</th><th class="pb-2 pr-4">{{ __('Latest') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($definitions as $definition)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $definition->name }}</td>
                            <td class="py-2 pr-4">{{ $definition->category }}</td>
                            <td class="py-2 pr-4">{{ $definition->unit }}</td>
                            <td class="py-2 pr-4">{{ $definition->latestValue?->value ?? '-' }}</td>
                            <td class="py-2"><a href="{{ route('kpitool.definitions.show', $definition) }}" class="text-indigo-600">{{ __('View') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $definitions->links() }}</div>
        </div>
    </div>
@endsection
