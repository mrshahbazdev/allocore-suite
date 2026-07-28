@extends('layouts.shell', ['title' => __('Decision Log')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('visionflow.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ __('Decision Log') }}</span>
    </div>

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('Decision Log') }}</h1>
        <a href="{{ route('visionflow.organizations.decision-logs.create', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('New Decision Log') }}
        </a>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('No decision log yet') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Create your first decision log to build your organization alignment.') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __("Title") }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __("Value") }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __("Mission") }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __("By") }}</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($items as $item)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4 text-sm text-slate-900">{{ $item->title }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $item->value->title ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $item->mission->title ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $item->user->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    
                                    <a href="{{ route('visionflow.organizations.decision-logs.edit', [$organization, $item]) }}" class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200" title="{{ __('Edit') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('visionflow.organizations.decision-logs.destroy', [$organization, $item]) }}" onsubmit="return confirm('{{ __('Delete this decision log?') }}')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100" title="{{ __('Delete') }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397M4.772 5.79L5.42 3.356A2.25 2.25 0 017.47 2.25h9.06a2.25 2.25 0 012.05 2.106l.648 2.434m-14.456 0a48.11 48.11 0 013.478-.397"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
