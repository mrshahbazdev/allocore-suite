@extends('layouts.public')

@section('title', $caseStudy->title)
@section('meta_description', Str::limit(strip_tags($caseStudy->challenge), 160))

@section('content')
    <section class="py-16 lg:py-24">
        <div class="mx-auto max-w-4xl px-6 lg:px-8">
            <a href="{{ route('case-studies.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">&larr; {{ __('All case studies') }}</a>

            <div class="mt-6 text-center">
                @if ($caseStudy->image)
                    <img src="{{ $caseStudy->image }}" alt="" class="mx-auto mb-6 h-64 w-full rounded-2xl object-cover">
                @endif
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">{{ $caseStudy->industry }}</p>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">{{ $caseStudy->title }}</h1>
                @if ($caseStudy->company)
                    <p class="mt-2 text-lg text-slate-500">{{ $caseStudy->company }}</p>
                @endif
            </div>

            <div class="mt-12 space-y-10">
                @if ($caseStudy->challenge)
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ __('Ausgangslage') }}</h2>
                        <div class="mt-3 text-slate-700">{{ $caseStudy->challenge }}</div>
                    </div>
                @endif

                @if ($caseStudy->solution)
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ __('Umsetzung') }}</h2>
                        <div class="mt-3 text-slate-700">{{ $caseStudy->solution }}</div>
                    </div>
                @endif

                @if ($caseStudy->result)
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ __('Ergebnis') }}</h2>
                        <div class="mt-3 text-slate-700">{{ $caseStudy->result }}</div>
                    </div>
                @endif

                @if (! empty($caseStudy->metrics))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                        <h2 class="text-xl font-bold text-slate-900">{{ __('Kennzahlen') }}</h2>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($caseStudy->metrics as $label => $value)
                                <div class="rounded-xl border border-slate-200 bg-white p-4">
                                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                                    <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
