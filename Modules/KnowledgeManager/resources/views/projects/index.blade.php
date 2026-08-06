@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Knowledge Projects') }}</h1>
        <a href="{{ route('knowledgemanager.projects.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('New Project') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($projects->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No projects yet.') }}</p>
        @else
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">{{ __('Name') }}</th>
                        <th class="py-2 pr-4">{{ __('Status') }}</th>
                        <th class="py-2 pr-4">{{ __('Progress') }}</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($projects as $project)
                        <tr>
                            <td class="py-3 pr-4">
                                <a href="{{ route('knowledgemanager.projects.show', $project) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $project->name }}</a>
                            </td>
                            <td class="py-3 pr-4">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $project->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($project->status) }}</span>
                            </td>
                            <td class="py-3 pr-4">
                                <div class="h-2 w-24 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-[#0094af]" style="width: {{ $project->progress() }}%"></div>
                                </div>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('knowledgemanager.projects.edit', $project) }}" class="text-sm text-[#0094af] hover:underline">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $projects->links() }}</div>
        @endif
    </div>
</div>
@endsection
