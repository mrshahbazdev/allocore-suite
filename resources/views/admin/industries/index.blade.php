@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Industries') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage clusters and sub-industries used for audits and benchmarking.') }}</p>
        </div>
        <a href="{{ route('admin.industries.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Add industry') }}</a>
    </div>

    <div class="mb-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">{{ __('Clusters') }}</div>
        <div class="divide-y divide-slate-100">
            @forelse ($clusters as $cluster)
                <div class="flex items-start justify-between p-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ $cluster->name }}</p>
                        @if ($cluster->children->isNotEmpty())
                            <p class="mt-1 text-sm text-slate-500">{{ $cluster->children->pluck('name')->implode(', ') }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.industries.edit', $cluster) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('admin.industries.destroy', $cluster) }}" onsubmit="return confirm('{{ __('Delete this cluster?') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-slate-400">{{ __('No industries yet.') }}</div>
            @endforelse
        </div>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">{{ __('Sub-industries') }}</div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Cluster') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($subIndustries as $sub)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $sub->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $sub->parent?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.industries.edit', $sub) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.industries.destroy', $sub) }}" onsubmit="return confirm('{{ __('Delete this sub-industry?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">{{ __('No sub-industries yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $subIndustries->links() }}</div>
@endsection
