@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Ideas') }}</h1>
        <a href="{{ route('devmanager.ideas.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('New Idea') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($ideas->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No ideas yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-slate-100 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="pb-3 pr-4">{{ __('Title') }}</th>
                            <th class="pb-3 pr-4">{{ __('Status') }}</th>
                            <th class="pb-3 pr-4">{{ __('Requirements') }}</th>
                            <th class="pb-3 pr-4">{{ __('Stories') }}</th>
                            <th class="pb-3 pr-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($ideas as $idea)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-900"><a href="{{ route('devmanager.ideas.show', $idea) }}" class="hover:text-[#ff9200]">{{ $idea->title }}</a></td>
                                <td class="py-3 pr-4 text-slate-600">{{ $idea->status }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $idea->requirements_count }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $idea->user_stories_count }}</td>
                                <td class="py-3 pr-4 text-right">
                                    <a href="{{ route('devmanager.ideas.edit', $idea) }}" class="text-sm text-[#0094af] hover:underline">{{ __('Edit') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $ideas->links() }}</div>
        @endif
    </div>
</div>
@endsection
