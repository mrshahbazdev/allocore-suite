@extends('layouts.public')

@section('title', __('Case Studies'))
@section('meta_description', __('Discover how companies use Allocore to mature across the corporate needs pyramid.'))

@section('content')
    <section class="py-16 lg:py-24">
        <div class="mx-auto max-w-5xl px-6 lg:px-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">{{ __('Success stories') }}</p>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">{{ __('Case Studies') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">{{ __('Real examples of how companies develop with the Allocore Framework.') }}</p>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-white py-16">
        <div class="mx-auto max-w-6xl px-6 lg:px-8">
            @if ($caseStudies->isEmpty())
                <p class="text-center text-slate-500">{{ __('No case studies published yet.') }}</p>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($caseStudies as $caseStudy)
                        <a href="{{ route('case-studies.show', $caseStudy) }}" class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md">
                            @if ($caseStudy->image)
                                <img src="{{ $caseStudy->image }}" alt="" class="mb-4 h-40 w-full rounded-xl object-cover">
                            @endif
                            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ $caseStudy->industry ?? __('Case study') }}</p>
                            <h2 class="mt-2 text-lg font-bold text-slate-900 group-hover:text-indigo-600">{{ $caseStudy->title }}</h2>
                            @if ($caseStudy->company)
                                <p class="mt-1 text-sm text-slate-500">{{ $caseStudy->company }}</p>
                            @endif
                            <p class="mt-3 line-clamp-3 text-sm text-slate-600">{{ $caseStudy->challenge }}</p>
                            @if (! empty($caseStudy->metrics))
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($caseStudy->metrics as $label => $value)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $label }}: {{ $value }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="mt-10">{{ $caseStudies->links() }}</div>
            @endif
        </div>
    </section>
@endsection
