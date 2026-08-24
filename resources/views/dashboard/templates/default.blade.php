    <style>
        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(1rem); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            0% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes slideInLeft {
            0% { opacity: 0; transform: translateX(-1rem); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeUpDim {
            0% { opacity: 0; transform: translateY(1rem); }
            100% { opacity: 0.9; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-scale-in { animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-slide-in-left { animation: slideInLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-fade-up-dim { animation: fadeUpDim 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    {{-- Announcements --}}
    @if ($announcements->isNotEmpty())
        <div class="mb-6 space-y-3">
            @foreach ($announcements as $announcement)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-900 opacity-0 animate-slide-in-left" style="animation-delay: {{ $loop->index * 80 }}ms">
                    <h2 class="font-semibold">{{ $announcement->title }}</h2>
                    <p class="mt-1 text-sm text-blue-800">{{ $announcement->body }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mx-auto max-w-5xl">
        @if ($allocoreScore)
            {{-- Allocore Score --}}
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8 opacity-0 animate-fade-up" style="animation-delay: 0ms">
                <div class="flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium uppercase tracking-wider text-slate-500">{{ __('Allocore Score') }}</p>
                        <div class="mt-2 flex flex-wrap items-end gap-3">
                            <span class="text-5xl font-extrabold text-slate-900 lg:text-6xl">{{ $allocoreScore->score }}</span>
                            <div class="mb-2 flex flex-col items-start gap-1 sm:flex-row sm:items-center">
                                <span class="rounded-full px-3 py-1 text-sm font-semibold
                                    {{ match($allocoreScore->maturity_level) { 'Excellent' => 'bg-emerald-100 text-emerald-700', 'Strong' => 'bg-green-100 text-green-700', 'Solid' => 'bg-blue-100 text-blue-700', 'Weak' => 'bg-amber-100 text-amber-700', default => 'bg-red-100 text-red-700' } }}">
                                    {{ __($allocoreScore->maturity_level) }}
                                </span>
                                @if (($allocoreCoach['trend']['direction'] ?? 'same') !== 'same')
                                    <span class="inline-flex items-center gap-1 text-sm font-semibold {{ $allocoreCoach['trend']['direction'] === 'up' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        @if ($allocoreCoach['trend']['direction'] === 'up')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25"/></svg>
                                        @endif
                                        {{ $allocoreCoach['trend']['delta'] }} {{ __('points') }} {{ $allocoreCoach['trend']['text'] }}
                                    </span>
                                @else
                                    <span class="text-sm font-medium text-slate-500">{{ __('first score') }}</span>
                                @endif
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ __('out of 100') }} &middot; {{ $allocoreScore->calculated_at->diffForHumans() }}</p>
                    </div>
                    <div class="w-full max-w-2xl lg:w-2/3">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($allocoreScore->pillars as $pillar)
                                <div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-slate-700">{{ __($pillar['name']) }}</span>
                                        <span class="font-semibold text-slate-900">{{ $pillar['score'] }}</span>
                                    </div>
                                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-[#ff9200]" style="width: {{ min(100, max(0, $pillar['score'])) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('allocore-score.index') }}" class="mt-4 inline-block text-sm font-semibold text-[#ff9200] hover:underline">{{ __('View score history') }}</a>
                    </div>
                </div>
            </div>

            @include('dashboard.partials.coach')
        @else
            <div class="mb-6 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600 opacity-0 animate-fade-up" style="animation-delay: 80ms">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#ff9200]/10 text-[#ff9200]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.261 4.14L21 12.84M4.5 17.25h12.75m-12.75 0a1.5 1.5 0 01-1.5-1.5M4.5 17.25a1.5 1.5 0 01-1.5-1.5M17.25 4.5v11.25m0 0l1.5 1.5m-1.5-1.5l-1.5 1.5"/></svg>
                </div>
                <p class="mt-4 font-semibold">{{ __('Discover your Allocore Score') }}</p>
                <p class="mt-1 text-sm">{{ __('Run an AuditPro assessment to see where your company stands on the corporate needs pyramid.') }}</p>
                <a href="{{ route('audit.index') }}" class="mt-4 inline-block rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">{{ __('Start audit') }}</a>
            </div>
        @endif

        {{-- Quick actions --}}
        <div class="grid gap-4 opacity-0 animate-fade-up sm:grid-cols-3" style="animation-delay: 160ms">
            <a href="{{ route('audit.index') }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-[#ff9200] hover:shadow-md">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#ff9200]/10 text-[#ff9200]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.108v8.842a2.25 2.25 0 002.25 2.25z"/></svg>
                </div>
                <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-[#ff9200]">{{ __('Start audit') }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ __('Get your maturity score and personalized next steps.') }}</p>
            </a>
            <a href="{{ route('tools.index') }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-[#ff9200] hover:shadow-md">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#0094af]/10 text-[#0094af]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                </div>
                <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-[#ff9200]">{{ __('My tools') }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ __('Open the tools that are active for your company.') }}</p>
            </a>
            <a href="{{ route('help.index') }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-[#ff9200] hover:shadow-md">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#ff9200]/10 text-[#ff9200]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.026 2.999-1.026 4.17 0 1.172 1.026 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                </div>
                <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-[#ff9200]">{{ __('Help Center') }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ __('Learn how to use Allocore and grow your business.') }}</p>
            </a>
        </div>
    </div>
