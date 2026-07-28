@extends('layouts.shell', ['title' => __('Visions')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('visionflow.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ __('Visions') }}</span>
    </div>

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('Visions') }}</h1>
        <a href="{{ route('visionflow.organizations.visions.create', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('New Vision') }}
        </a>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.214.13.428.183.641M9.954 9.784c-.463.598-.697 1.304-.697 2.016 0 .713.234 1.419.697 2.016m4.092-4c.463.597.697 1.303.697 2.016 0 .713-.234 1.419-.697 2.016M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('No visions yet') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Create your first vision to build your organization alignment.') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __("Content") }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __("Status") }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __("Version") }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __("Current") }}</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($items as $item)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4 text-sm text-slate-900">{{ $item->content }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ strtolower($item->status) == 'approved' || strtolower($item->status) == 'active' || strtolower($item->status) == 'completed' || strtolower($item->status) == 'current' ? 'bg-emerald-100 text-emerald-700' : (strtolower($item->status) == 'archived' || strtolower($item->status) == 'on_hold' || strtolower($item->status) == 'paused' ? 'bg-slate-100 text-slate-600' : (strtolower($item->status) == 'proposed' || strtolower($item->status) == 'reviewing' || strtolower($item->status) == 'drafting' ? 'bg-amber-100 text-amber-700' : (strtolower($item->status) == 'draft' ? 'bg-slate-100 text-slate-600' : 'bg-slate-100 text-slate-700')))) }}">{{ $item->status }}</span></td>
                            <td class="px-5 py-4 text-sm text-slate-900">{{ $item->version }}</td>
                            <td class="px-5 py-4 text-sm text-slate-900">{{ $item->is_current }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('visionflow.organizations.visions.approve', [$organization, $item]) }}" class="inline">
                                        @csrf
                                        <button class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2.5 py-1.5 text-xs font-semibold">{{ __("Approve") }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('visionflow.organizations.visions.current', [$organization, $item]) }}" class="inline">
                                        @csrf
                                        <button class="inline-flex items-center gap-1 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 px-2.5 py-1.5 text-xs font-semibold">{{ __("Set Current") }}</button>
                                    </form>
                                    
                                    <a href="{{ route('visionflow.organizations.visions.edit', [$organization, $item]) }}" class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200" title="{{ __('Edit') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('visionflow.organizations.visions.destroy', [$organization, $item]) }}" onsubmit="return confirm('{{ __('Delete this vision?') }}')" class="inline">
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
