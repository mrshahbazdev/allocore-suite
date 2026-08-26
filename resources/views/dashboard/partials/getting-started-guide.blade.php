<div class="mb-8 overflow-hidden rounded-3xl border border-orange-200/80 bg-gradient-to-br from-amber-50/70 via-white to-orange-50/50 p-6 shadow-sm sm:p-8 opacity-0 animate-fade-up" style="animation-delay: 60ms">
    {{-- Header: Friendly Welcome & Value Pitch --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3.5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#ff9200] text-white shadow-md shadow-[#ff9200]/30 text-xl">
                🚀
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">{{ __('How Allocore works — In 3 easy steps') }}</h2>
                <p class="mt-0.5 text-sm text-slate-600">{{ __('Find business leaks, get your health score, and fix bottlenecks with built-in tools.') }}</p>
            </div>
        </div>
        @if ($allocoreScore)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                {{ __('Assessment completed') }}
            </span>
        @endif
    </div>

    {{-- 3 Simple Steps --}}
    <div class="mt-8 grid gap-6 md:grid-cols-3">
        {{-- Step 1: 5-Min Audit --}}
        <div class="relative flex flex-col justify-between rounded-2xl border-2 {{ $allocoreScore ? 'border-emerald-300 bg-emerald-50/40' : 'border-[#ff9200] bg-white' }} p-6 shadow-sm transition hover:shadow-md">
            <span class="absolute -top-3 left-6 rounded-full {{ $allocoreScore ? 'bg-emerald-600' : 'bg-[#ff9200]' }} px-3 py-0.5 text-xs font-bold text-white uppercase tracking-wider">
                {{ $allocoreScore ? '✓ ' . __('Step 1 done') : __('Step 1') }}
            </span>
            <div>
                <div class="mt-2 text-3xl">📋</div>
                <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('5-Minute Business Check') }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ __('Answer 25 quick yes/no questions about your sales, cash flow, delivery, and team.') }}</p>
            </div>
            <div class="mt-6">
                @if ($allocoreScore)
                    <a href="{{ route('audit.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                        {{ __('Retake audit') }}
                    </a>
                @else
                    <a href="{{ route('audit.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#ff9200] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:opacity-90">
                        {{ __('Start 5-min Check') }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @endif
            </div>
        </div>

        {{-- Step 2: Health Score & AI Diagnosis --}}
        <div class="relative flex flex-col justify-between rounded-2xl border {{ $allocoreScore ? 'border-indigo-300 bg-indigo-50/40' : 'border-slate-200 bg-white/80' }} p-6 shadow-sm">
            <span class="absolute -top-3 left-6 rounded-full {{ $allocoreScore ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700' }} px-3 py-0.5 text-xs font-bold uppercase tracking-wider">
                {{ $allocoreScore ? '✓ ' . __('Step 2 ready') : __('Step 2') }}
            </span>
            <div>
                <div class="mt-2 text-3xl">🎯</div>
                <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('See Score & #1 Bottleneck') }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ __('Get your health score (0-100) and compare with similar companies to see what is holding you back.') }}</p>
            </div>
            <div class="mt-6">
                @if ($allocoreScore)
                    <a href="{{ route('allocore-score.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        {{ __('View Score & Diagnosis') }} ({{ $allocoreScore->score }}/100)
                    </a>
                @else
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 py-2.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <span>{{ __('Unlocks automatically after Step 1') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Step 3: Fix with Built-in Tools --}}
        <div class="relative flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <span class="absolute -top-3 left-6 rounded-full bg-slate-200 px-3 py-0.5 text-xs font-bold text-slate-700 uppercase tracking-wider">
                {{ __('Step 3') }}
            </span>
            <div>
                <div class="mt-2 text-3xl">🛠️</div>
                <h3 class="mt-3 text-base font-bold text-slate-900">{{ __('Fix & Scale with Tools') }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ __('Use built-in apps (Cash Flow, Invoices, SOPs, Tasks) recommended by your Coach to grow.') }}</p>
            </div>
            <div class="mt-6">
                <a href="{{ route('tools.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    {{ __('Explore 20+ Tools') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Bottom Value Highlights (Why Allocore?) --}}
    <div class="mt-8 grid gap-4 pt-6 border-t border-orange-200/60 sm:grid-cols-3 text-xs text-slate-700">
        <div class="flex items-center gap-2.5">
            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-bold text-[11px]">✓</span>
            <span><strong>{{ __('No guesswork:') }}</strong> {{ __('Know exactly what to fix first.') }}</span>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-bold text-[11px]">✓</span>
            <span><strong>{{ __('All-in-one:') }}</strong> {{ __('20+ business tools included.') }}</span>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-bold text-[11px]">✓</span>
            <span><strong>{{ __('Track progress:') }}</strong> {{ __('Watch your company maturity score rise.') }}</span>
        </div>
    </div>
</div>
