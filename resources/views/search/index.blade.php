@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Search') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Find records across all your subscribed tools.') }}</p>
    </div>

    <form method="GET" action="{{ route('search.index') }}" class="mb-8">
        <div class="flex gap-2">
            <input type="search" name="q" value="{{ $query }}" placeholder="{{ __('Search contacts, invoices, projects...') }}" class="flex-1 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </div>
    </form>

    @if ($query !== '')
        @if (empty($results))
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No results for') }} "{{ $query }}".</div>
        @else
            <div class="space-y-6">
                @foreach ($results as $group)
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ $group['module'] }}</h2>
                        <ul class="divide-y divide-slate-100">
                            @foreach ($group['records'] as $record)
                                <li class="py-2">
                                    <a href="{{ $record['url'] }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ $record['title'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
@endsection
