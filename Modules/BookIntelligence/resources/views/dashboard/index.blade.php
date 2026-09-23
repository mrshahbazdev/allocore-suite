@php $title = __('Knowledge Growth OS — Executive Dashboard'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-8" data-no-navigate>
        <!-- Executive Header Banner -->
        <section class="overflow-hidden rounded-3xl bg-slate-950 shadow-sm">
            <div class="grid gap-8 px-6 py-8 sm:px-8 lg:grid-cols-[1.5fr_1fr] lg:items-center lg:px-10 lg:py-10">
                <div>
                    <span class="inline-flex rounded-full bg-[#ff9200]/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#ffb34d]">
                        {{ __('Allocore Knowledge Growth OS') }}
                    </span>
                    <h1 class="mt-4 max-w-2xl text-3xl font-extrabold tracking-tight text-white sm:text-4xl leading-tight">
                        {{ __('Unified Knowledge, Competency, Content & Monetization OS') }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                        {{ __('Transforming organizational book intelligence into employee skill progression, automated SEO content creation, and affiliate monetization.') }}
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('bookintelligence.search.index') }}" class="rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:from-orange-600 hover:to-orange-700">
                            🔍 {{ __('Ask Knowledge Search') }}
                        </a>
                        <a href="{{ route('bookintelligence.content.index') }}" class="rounded-xl bg-white px-5 py-2.5 text-xs font-bold text-slate-900 shadow-sm hover:bg-slate-100">
                            ⚡ {{ __('AI Content Engine') }}
                        </a>
                        <a href="{{ route('bookintelligence.competency.index') }}" class="rounded-xl border border-slate-700 bg-slate-900 px-5 py-2.5 text-xs font-bold text-white hover:bg-slate-800">
                            🪜 {{ __('Competencies & Careers') }}
                        </a>
                    </div>
                </div>

                <!-- Executive Highlights Card -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 space-y-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#0094af]">{{ __('Knowledge OS Health & Pacing') }}</p>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="rounded-xl bg-slate-800/80 p-3">
                            <span class="text-slate-400 block text-[11px] uppercase">Library Intelligence</span>
                            <strong class="text-lg font-extrabold text-white">{{ $stats['analyzed'] }}/{{ $stats['books'] }}</strong>
                            <span class="text-emerald-400 block text-[10px]">Ready for Q&A</span>
                        </div>
                        <div class="rounded-xl bg-slate-800/80 p-3">
                            <span class="text-slate-400 block text-[11px] uppercase">Knowledge Gaps</span>
                            <strong class="text-lg font-extrabold text-amber-400">{{ $stats['open_gaps'] }}</strong>
                            <span class="text-slate-400 block text-[10px]">Unresolved Queries</span>
                        </div>
                        <div class="rounded-xl bg-slate-800/80 p-3">
                            <span class="text-slate-400 block text-[11px] uppercase">Articles Published</span>
                            <strong class="text-lg font-extrabold text-white">{{ $stats['published_blogs'] }}</strong>
                            <span class="text-blue-400 block text-[10px]">To Allocore CMS</span>
                        </div>
                        <div class="rounded-xl bg-slate-800/80 p-3">
                            <span class="text-slate-400 block text-[11px] uppercase">Affiliate Monetization</span>
                            <strong class="text-lg font-extrabold text-emerald-400">€{{ number_format($stats['affiliate_revenue'], 2) }}</strong>
                            <span class="text-slate-400 block text-[10px]">{{ $stats['affiliate_clicks'] }} clicks</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- The 4 Core Operating Pillars Metrics Grid -->
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <!-- Pillar 1: Knowledge Metrics -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-bold uppercase text-blue-700">Pillar 1</span>
                    <span class="text-lg">📚</span>
                </div>
                <h3 class="text-base font-bold text-slate-900">{{ __('Knowledge Base') }}</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Books in Library</span>
                        <strong class="text-slate-900 font-bold">{{ $stats['books'] }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">FAQ Question Mappings</span>
                        <strong class="text-slate-900 font-bold">{{ $stats['faqs'] }}</strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Unresolved Gaps</span>
                        <strong class="text-amber-600 font-bold">{{ $stats['open_gaps'] }}</strong>
                    </div>
                </div>
                <a href="{{ route('bookintelligence.questions.index') }}" class="block text-center rounded-xl bg-slate-50 py-2 text-xs font-bold text-[#0094af] hover:bg-slate-100">
                    {{ __('Explore FAQs & Gaps') }} &rarr;
                </a>
            </div>

            <!-- Pillar 2: Employee Competency Metrics -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold uppercase text-emerald-700">Pillar 2</span>
                    <span class="text-lg">🎓</span>
                </div>
                <h3 class="text-base font-bold text-slate-900">{{ __('Employee Growth') }}</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Active Learning Paths</span>
                        <strong class="text-slate-900 font-bold">{{ $stats['learning_paths'] }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Assessments Passed</span>
                        <strong class="text-emerald-700 font-bold">{{ $stats['assessments_passed'] }}</strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Challenges Completed</span>
                        <strong class="text-purple-700 font-bold">{{ $stats['challenges_completed'] }}</strong>
                    </div>
                </div>
                <a href="{{ route('bookintelligence.expertise.index') }}" class="block text-center rounded-xl bg-slate-50 py-2 text-xs font-bold text-emerald-700 hover:bg-slate-100">
                    {{ __('View Mastery Leaderboard') }} &rarr;
                </a>
            </div>

            <!-- Pillar 3: Content & SEO Metrics -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <span class="rounded-md bg-purple-50 px-2.5 py-1 text-xs font-bold uppercase text-purple-700">Pillar 3</span>
                    <span class="text-lg">📈</span>
                </div>
                <h3 class="text-base font-bold text-slate-900">{{ __('Content & SEO') }}</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">SEO Opportunities</span>
                        <strong class="text-slate-900 font-bold">{{ $stats['seo_opportunities'] }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">8-Section Blogs Generated</span>
                        <strong class="text-purple-700 font-bold">{{ $stats['generated_blogs'] }}</strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Published to CMS</span>
                        <strong class="text-emerald-700 font-bold">{{ $stats['published_blogs'] }}</strong>
                    </div>
                </div>
                <a href="{{ route('bookintelligence.content.index') }}" class="block text-center rounded-xl bg-slate-50 py-2 text-xs font-bold text-purple-700 hover:bg-slate-100">
                    {{ __('Open Content Engine') }} &rarr;
                </a>
            </div>

            <!-- Pillar 4: Affiliate Revenue Metrics -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-bold uppercase text-amber-800">Pillar 4</span>
                    <span class="text-lg">🛒</span>
                </div>
                <h3 class="text-base font-bold text-slate-900">{{ __('Affiliate Monetization') }}</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Total Book Clicks</span>
                        <strong class="text-slate-900 font-bold">{{ $stats['affiliate_clicks'] }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Conversion Rate</span>
                        <strong class="text-slate-900 font-bold">{{ $stats['conversion_rate'] }}%</strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Commission Earned</span>
                        <strong class="text-emerald-700 font-bold">€{{ number_format($stats['affiliate_revenue'], 2) }}</strong>
                    </div>
                </div>
                <a href="{{ route('bookintelligence.affiliate.index') }}" class="block text-center rounded-xl bg-slate-50 py-2 text-xs font-bold text-amber-800 hover:bg-slate-100">
                    {{ __('View Affiliate Analytics') }} &rarr;
                </a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Team Competency Leaderboard Widget -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>🏆</span> {{ __('Top Team Knowledge Leaders') }}
                    </h3>
                    <a href="{{ route('bookintelligence.expertise.index') }}" class="text-xs font-bold text-[#0094af] hover:underline">
                        {{ __('Full Leaderboard') }} &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse ($teamProfiles as $idx => $profile)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-700">{{ $idx + 1 }}</span>
                                <div>
                                    <h4 class="font-bold text-slate-900">{{ $profile->user?->name ?? 'Teammate' }}</h4>
                                    <p class="text-[11px] text-slate-400">{{ $profile->targetRole?->name ?? 'Career Learner' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="rounded bg-orange-100 px-2 py-0.5 font-bold uppercase text-orange-800 text-[10px]">{{ $profile->expertise_level }}</span>
                                <p class="text-[11px] font-extrabold text-[#ff9200] mt-0.5">{{ $profile->points }} XP</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-slate-400">{{ __('No team expertise profiles created yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Searches & Knowledge Inquiries -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>🔍</span> {{ __('Recent Knowledge Inquiries') }}
                    </h3>
                    <a href="{{ route('bookintelligence.search.index') }}" class="text-xs font-bold text-[#0094af] hover:underline">
                        {{ __('Knowledge Search') }} &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse ($recentSearches as $search)
                        <div class="flex items-center justify-between py-3">
                            <div class="min-w-0 flex-1 pr-3">
                                <a href="{{ route('bookintelligence.search.index', ['q' => $search->query]) }}" class="font-bold text-slate-800 hover:text-[#0094af] truncate block">
                                    "{{ $search->query }}"
                                </a>
                                <span class="text-[11px] text-slate-400">{{ $search->created_at->diffForHumans() }}</span>
                            </div>
                            <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $search->has_results ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $search->has_results ? 'Answered' : 'Gap Logged' }}
                            </span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-slate-400">{{ __('No recent knowledge searches.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
