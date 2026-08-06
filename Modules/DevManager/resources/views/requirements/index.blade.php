@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('devmanager.ideas.show', $idea) }}" class="text-sm text-[#0094af] hover:underline">&larr; {{ $idea->title }}</a>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Requirements') }}</h1>
        </div>
        <a href="{{ route('devmanager.requirements.create', $idea) }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Add Requirement') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($requirements->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No requirements yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-slate-100 text-left text-xs uppercase text-slate-500"><tr><th class="pb-3 pr-4">{{ __('Title') }}</th><th class="pb-3 pr-4">{{ __('Priority') }}</th><th class="pb-3 pr-4">{{ __('Status') }}</th><th class="pb-3 pr-4 text-right">{{ __('Actions') }}</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($requirements as $requirement)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-900"><a href="{{ route('devmanager.requirements.show', $requirement) }}" class="hover:text-[#ff9200]">{{ $requirement->title }}</a></td>
                                <td class="py-3 pr-4 text-slate-600">{{ $requirement->priority }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $requirement->status }}</td>
                                <td class="py-3 pr-4 text-right"><a href="{{ route('devmanager.requirements.edit', $requirement) }}" class="text-sm text-[#0094af] hover:underline">{{ __('Edit') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $requirements->links() }}</div>
        @endif
    </div>
</div>
@endsection
