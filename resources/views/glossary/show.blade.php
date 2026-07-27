@extends('layouts.public')

@section('content')
    <div class="bg-slate-900 py-16 text-white">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('glossary.index') }}" class="text-sm text-indigo-300 hover:text-white">← {{ __('Back to glossary') }}</a>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">{{ $glossary->term }}</h1>
            @if ($glossary->category)
                <span class="mt-3 inline-block rounded-full bg-indigo-800 px-3 py-1 text-xs font-semibold text-indigo-100">{{ $glossary->category }}</span>
            @endif
            @if ($glossary->is_beginner_friendly)
                <span class="mt-3 ml-2 inline-block rounded-full bg-emerald-800 px-3 py-1 text-xs font-semibold text-emerald-100">{{ __('Beginner-friendly') }}</span>
            @endif
        </div>
    </div>

    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($glossary->simple_definition)
            <div class="mb-8 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-6">
                <p class="text-sm font-semibold uppercase tracking-wider text-emerald-800">{{ __('Simple explanation') }}</p>
                <p class="mt-2 text-lg leading-relaxed text-emerald-900">{{ $glossary->simple_definition }}</p>
            </div>
        @endif

        <div class="prose prose-slate max-w-none">
            <p class="text-lg leading-relaxed text-slate-700">{{ $glossary->definition }}</p>
        </div>

        @if (! empty($glossary->related_modules))
            <div class="mt-8">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('Related tools') }}</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($glossary->related_modules as $module)
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">{{ $module }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
