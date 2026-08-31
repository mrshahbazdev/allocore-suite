@extends('layouts.shell')

@section('content')
    @php($pageTitle = $knowledge->term)

    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('knowledge.index') }}" class="text-sm text-[#0094af] hover:underline">{{ __('Back to knowledge') }}</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $knowledge->term }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if ($knowledge->category)
                    <span class="badge badge-gray">{{ $knowledge->category }}</span>
                @endif
                @if ($knowledge->is_beginner_friendly)
                    <span class="badge badge-green">{{ __('Beginner-friendly') }}</span>
                @endif
            </div>
        </div>
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('admin.glossary.edit', $knowledge) }}" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                {{ __('Edit') }}
            </a>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            @if ($knowledge->simple_definition)
                <div class="rounded-xl border-l-4 border-[#ff9200] bg-[#ff9200]/5 p-5">
                    <p class="text-sm font-semibold uppercase tracking-wider text-[#ff9200]">{{ __('Simple explanation') }}</p>
                    <p class="mt-2 text-lg leading-relaxed text-slate-900">{{ $knowledge->simple_definition }}</p>
                </div>
            @endif

            <div class="card">
                <p class="whitespace-pre-line text-slate-700 leading-relaxed">{{ $knowledge->definition }}</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card">
                <h2 class="card-title">
                    <svg class="h-5 w-5 text-[#ff9200]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.085a6.659 6.659 0 00-1.508-11.623 6.659 6.659 0 00-1.508 11.623c.85.262 1.508 1.102 1.508 2.085V18"/></svg>
                    {{ __('Further information') }}
                </h2>
                <p class="text-sm text-slate-600">{{ __('This article is part of the Allocore knowledge base. Use it to understand the topic and take the next step.') }}</p>
                @if (! empty($knowledge->related_modules))
                    <div class="mt-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Related tools') }}</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($knowledge->related_modules as $module)
                                <span class="badge badge-gray">{{ $module }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
                <a href="{{ route('glossary.show', $knowledge) }}" target="_blank" class="btn btn-secondary btn-sm mt-4">
                    {{ __('Open public glossary') }}
                    <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                </a>
            </div>
        </div>
    </div>
@endsection
