@php $title = __('AI Content & SEO Intelligence Engine'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
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

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 6 & 7 — Content Growth & Blog Engine') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('AI Content & SEO Intelligence Engine') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Discovers high-traffic SEO keywords and user pain points across your book library, and generates publication-ready 8-section structured blog articles.') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('bookintelligence.content.discover') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:from-orange-600 hover:to-orange-700">
                        ⚡ {{ __('Discover New Opportunities') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-400">{{ __('Total Opportunities') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-blue-700">{{ __('Blog Guides') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-blue-900">{{ $stats['blogs'] }}</p>
            </div>
            <div class="rounded-2xl border border-purple-200 bg-purple-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-purple-700">{{ __('FAQs & Problems') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-purple-900">{{ $stats['faqs'] }}</p>
            </div>
            <div class="rounded-2xl border border-cyan-200 bg-cyan-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-cyan-700">{{ __('LinkedIn Posts') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-cyan-900">{{ $stats['linkedin'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-emerald-700">{{ __('Published to Blog') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-900">{{ $stats['published'] }}</p>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('bookintelligence.content.index', ['type' => 'all', 'status' => $status]) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $type === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ __('All Types') }}
                </a>
                <a href="{{ route('bookintelligence.content.index', ['type' => 'blog', 'status' => $status]) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $type === 'blog' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Blogs') }}
                </a>
                <a href="{{ route('bookintelligence.content.index', ['type' => 'faq', 'status' => $status]) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $type === 'faq' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ __('FAQs') }}
                </a>
                <a href="{{ route('bookintelligence.content.index', ['type' => 'linkedin', 'status' => $status]) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $type === 'linkedin' ? 'bg-cyan-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ __('LinkedIn') }}
                </a>
                <a href="{{ route('bookintelligence.content.index', ['type' => 'whitepaper', 'status' => $status]) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $type === 'whitepaper' ? 'bg-slate-700 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Whitepapers') }}
                </a>
            </div>
        </div>

        <!-- Opportunities Grid -->
        @if ($opportunities->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl text-orange-600">
                    📈
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900">{{ __('No Content Opportunities Found') }}</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    {{ __('Click "Discover New Opportunities" above to let the AI scan your library, search queries, and industry keywords for high-traffic topics.') }}
                </p>
                <div class="mt-6">
                    <form method="POST" action="{{ route('bookintelligence.content.discover') }}">
                        @csrf
                        <button type="submit" class="rounded-xl bg-[#ff9200] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                            ⚡ {{ __('Run Opportunity Discovery') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($opportunities as $opp)
                    <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="rounded-md bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold uppercase text-slate-700">
                                        {{ $opp->content_type }}
                                    </span>
                                    @if ($opp->status === 'published' || $opp->generated_post_id)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-300">
                                            ● {{ __('Veröffentlicht') }}
                                        </span>
                                    @elseif ($opp->status === 'generated' || $opp->generatedBlog)
                                        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-800 border border-blue-300">
                                            ● {{ __('Generiert (Entwurf)') }}
                                        </span>
                                    @endif
                                </div>
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $opp->estimated_demand === 'high' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                    {{ $opp->estimated_demand }} {{ __('Demand') }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 leading-snug">
                                {{ $opp->title }}
                            </h3>

                            @if ($opp->target_keyword)
                                <p class="text-xs text-slate-500">
                                    🔑 <strong class="text-slate-700">{{ __('Target Keyword:') }}</strong> {{ $opp->target_keyword }}
                                </p>
                            @endif

                            @if ($opp->angle_hook)
                                <p class="text-xs text-slate-600 bg-slate-50 rounded-lg p-2.5 leading-relaxed">
                                    💡 <strong>{{ __('Angle / Hook:') }}</strong> {{ $opp->angle_hook }}
                                </p>
                            @endif

                            @if ($opp->book)
                                <div class="text-xs text-slate-500 pt-1">
                                    📖 {{ __('Source Book:') }} <strong class="text-slate-800">{{ $opp->book->title }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="mt-5 border-t border-slate-100 pt-4 space-y-2">
                            @if ($opp->generated_post_id && $opp->post)
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('blog.show', $opp->post->slug) }}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 shadow-sm transition">
                                        ✅ {{ __('Im Blog ansehen') }} ↗
                                    </a>
                                    @if ($opp->generatedBlog)
                                        <a href="{{ route('bookintelligence.content.blog.show', $opp->generatedBlog->id) }}" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                                            ✏️ {{ __('Editor') }}
                                        </a>
                                    @endif
                                </div>
                            @elseif ($opp->generatedBlog)
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('bookintelligence.content.blog.show', $opp->generatedBlog->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                                        📝 {{ __('Entwurf prüfen & veröffentlichen') }} &rarr;
                                    </a>
                                </div>
                            @else
                                <form method="POST" action="{{ route('bookintelligence.content.blog.generate') }}">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $opp->book_id ?? $books->first()?->id }}">
                                    <input type="hidden" name="opportunity_id" value="{{ $opp->id }}">
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 py-2.5 text-xs font-bold text-white hover:from-orange-600 hover:to-orange-700 shadow-sm transition">
                                        ⚡ {{ __('8-Abschnitte Blogartikel generieren') }} &rarr;
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $opportunities->links() }}
            </div>
        @endif
    </div>
@endsection
