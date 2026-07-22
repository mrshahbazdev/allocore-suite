@extends('layouts.shell')

@section('title', __('Processes'))
@section('page-title', __('Processes'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Processes') }}</h1>
            <a href="{{ route('loopengine.processes.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Process') }}</a>
        </div>

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-wrap items-end gap-3">
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Search') }}</label><input type="text" name="search" value="{{ request('search') }}" class="mt-1 rounded-lg border-slate-300"></div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="status" class="mt-1 rounded-lg border-slate-300">
                    <option value="">{{ __('All') }}</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>{{ __('Archived') }}</option>
                </select>
            </div>
            <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2 pr-4">{{ __('Version') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($processes as $process)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $process->localizedName() }}</td>
                            <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $process->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($process->status === 'draft' ? 'bg-slate-100 text-slate-700' : 'bg-rose-100 text-rose-700') }}">{{ __($process->status) }}</span></td>
                            <td class="py-2 pr-4">{{ $process->version }}</td>
                            <td class="py-2 flex gap-2">
                                <a href="{{ route('loopengine.processes.edit', $process) }}" class="text-indigo-600">{{ __('Builder') }}</a>
                                <a href="{{ route('loopengine.processes.show', $process) }}" class="text-slate-600">{{ __('Preview') }}</a>
                                <form method="POST" action="{{ route('loopengine.processes.duplicate', $process) }}" class="inline">@csrf<button class="text-slate-600">{{ __('Copy') }}</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $processes->links() }}</div>
        </div>
    </div>
@endsection
