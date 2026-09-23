@php $title = $blog->title; @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-5xl space-y-8" data-no-navigate>
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Top Navigation -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('bookintelligence.content.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; {{ __('Back to Content Engine') }}
            </a>
            <div class="flex items-center gap-3">
                @if ($blog->post_id)
                    <span class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                        ✅ {{ __('Published to CMS (Post #:id)', ['id' => $blog->post_id]) }}
                    </span>
                    <a href="{{ route('blog.show', $blog->slug) }}" target="_blank" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        {{ __('View Live Article') }} ↗
                    </a>
                @else
                    <form method="POST" action="{{ route('bookintelligence.content.blog.publish', $blog->id) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="category_id" class="rounded-xl border-slate-300 text-xs font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                            <option value="">{{ __('-- Select Category --') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700">
                            🚀 {{ __('Publish to Allocore CMS') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Article Header -->
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#0094af]">
                <span>📖</span> {{ __('Mandatory 8-Section Structured Blog Article') }}
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 sm:text-4xl leading-tight">
                {{ $blog->title }}
            </h1>
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-2 border-t border-slate-100">
                @if ($blog->target_keyword)
                    <span>🔑 <strong>Keyword:</strong> {{ $blog->target_keyword }}</span>
                @endif
                @if ($blog->book)
                    <span>📚 <strong>Source Book:</strong> {{ $blog->book->title }} ({{ $blog->book->author?->name }})</span>
                @endif
                <span>📅 <strong>Generated:</strong> {{ $blog->created_at->format('M d, Y') }}</span>
            </div>
            @if ($blog->meta_description)
                <p class="text-xs text-slate-600 bg-slate-50 rounded-xl p-3">
                    <strong>{{ __('Meta Description:') }}</strong> {{ $blog->meta_description }}
                </p>
            @endif
        </div>

        <!-- 8 Mandatory Sections Breakdown -->
        <div class="space-y-6">
            <!-- Section 1: Problem -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-red-600">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-xs">1</span>
                    {{ __('Section 1: Problem') }}
                </div>
                <div class="prose prose-slate mt-3 max-w-none text-slate-700 text-sm leading-relaxed">
                    {!! $blog->section_1_problem !!}
                </div>
            </div>

            <!-- Section 2: Root Causes -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-amber-600">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-xs">2</span>
                    {{ __('Section 2: Root Causes') }}
                </div>
                <div class="prose prose-slate mt-3 max-w-none text-slate-700 text-sm leading-relaxed">
                    {!! $blog->section_2_root_causes !!}
                </div>
            </div>

            <!-- Section 3: Solutions -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-blue-600">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-xs">3</span>
                    {{ __('Section 3: Strategic Solutions') }}
                </div>
                <div class="prose prose-slate mt-3 max-w-none text-slate-700 text-sm leading-relaxed">
                    {!! $blog->section_3_solutions !!}
                </div>
            </div>

            <!-- Section 4: Practical Implementation -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-emerald-600">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-xs">4</span>
                    {{ __('Section 4: Practical Implementation Steps') }}
                </div>
                <div class="prose prose-slate mt-3 max-w-none text-slate-700 text-sm leading-relaxed">
                    {!! $blog->section_4_implementation !!}
                </div>
            </div>

            <!-- Section 5: Common Mistakes -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-rose-600">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-100 text-xs">5</span>
                    {{ __('Section 5: Common Mistakes to Avoid') }}
                </div>
                <div class="prose prose-slate mt-3 max-w-none text-slate-700 text-sm leading-relaxed">
                    {!! $blog->section_5_common_mistakes !!}
                </div>
            </div>

            <!-- Section 6: Summary -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-purple-600">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-purple-100 text-xs">6</span>
                    {{ __('Section 6: Summary & Key Takeaways') }}
                </div>
                <div class="prose prose-slate mt-3 max-w-none text-slate-700 text-sm leading-relaxed">
                    {!! $blog->section_6_summary !!}
                </div>
            </div>

            <!-- Section 7: Call to Action -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-slate-800">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-xs">7</span>
                    {{ __('Section 7: Call to Action (CTA)') }}
                </div>
                <div class="prose prose-slate mt-3 max-w-none text-slate-800 text-sm leading-relaxed">
                    {!! $blog->section_7_cta !!}
                </div>
            </div>

            <!-- Section 8: Recommended Book Box (Affiliate Engine) -->
            <div class="rounded-3xl border-2 border-amber-300 bg-gradient-to-br from-amber-50/60 to-white p-8 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-amber-900">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-200 text-xs">8</span>
                    {{ __('Section 8: Recommended Book Box (Module 8 Affiliate Integration)') }}
                </div>
                @if ($blog->book)
                    <div class="mt-4 flex flex-col sm:flex-row items-center sm:items-start gap-6">
                        @if ($blog->book->cover_url)
                            <img src="{{ $blog->book->cover_url }}" alt="{{ $blog->book->title }}" class="h-36 w-24 rounded-xl object-cover shadow">
                        @else
                            <div class="flex h-36 w-24 items-center justify-center rounded-xl bg-amber-100 text-3xl shadow">📖</div>
                        @endif
                        <div class="flex-1 text-center sm:text-left space-y-2">
                            <h3 class="text-xl font-bold text-slate-900">{{ $blog->book->title }}</h3>
                            <p class="text-xs text-slate-500">{{ __('By') }} {{ $blog->book->author?->name ?? 'Author' }}</p>
                            <p class="text-xs text-slate-700 leading-relaxed">
                                {{ $blog->section_8_recommended_book['why_recommended'] ?? $blog->book->description }}
                            </p>
                            <div class="pt-2">
                                <a href="{{ route('bookintelligence.affiliate.redirect', ['book' => $blog->book->id, 'source' => 'blog']) }}" target="_blank" rel="noopener noreferrer sponsored" class="inline-flex items-center gap-2 rounded-xl bg-[#ff9200] px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-orange-600">
                                    🛒 {{ __('Get Book on Amazon') }} ↗
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
