@if ($allocoreCoach['has_score'] ?? false)
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8 opacity-0 animate-fade-up" style="animation-delay: 120ms">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#ff9200] text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Personal Allocore Coach') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Based on your latest score and your current maturity profile.') }}</p>
            </div>
        </div>

        @if ($allocoreCoach['benchmark'] ?? null)
            <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50/70 p-4 opacity-0 animate-fade-up" style="animation-delay: 140ms">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-900">{{ __('How you compare') }}</p>
                        <p class="text-sm text-blue-700">
                            @if ($allocoreCoach['benchmark']['percentile'] !== null)
                                {{ __('You score better than :percentile% of companies like yours.', ['percentile' => $allocoreCoach['benchmark']['percentile']]) }}
                            @elseif ($allocoreCoach['benchmark']['average'] !== null)
                                {{ __('Similar companies average :average points.', ['average' => $allocoreCoach['benchmark']['average']]) }}
                            @endif
                        </p>
                    </div>
                    @if ($allocoreCoach['benchmark']['cluster'])
                        <span class="badge badge-gray shrink-0">{{ $allocoreCoach['benchmark']['cluster'] }}</span>
                    @endif
                </div>
            </div>
        @endif

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            {{-- Positive --}}
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-5">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z"/></svg>
                    <h3 class="font-semibold text-emerald-900">{{ __('Something positive') }}</h3>
                </div>
                <p class="mt-2 text-sm font-medium text-emerald-800">{{ $allocoreCoach['positive']['headline'] }}</p>
                <p class="mt-1 text-sm text-emerald-700">{{ $allocoreCoach['positive']['detail'] }}</p>
            </div>

            {{-- Biggest problem + solution --}}
            @if ($allocoreCoach['problem'])
                <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-5">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12.75 17.25h.008v.008h-.008v-.008z"/></svg>
                        <h3 class="font-semibold text-rose-900">{{ __('Biggest problem & solution idea') }}</h3>
                    </div>
                    <p class="mt-2 text-sm font-medium text-rose-800">{{ $allocoreCoach['problem']['headline'] }} ({{ $allocoreCoach['problem']['score'] }}/100)</p>
                    <p class="mt-1 text-sm text-rose-700">{!! $allocoreCoach['problem']['solution'] !!}</p>
                </div>
            @endif

            {{-- Tool --}}
            @if ($allocoreCoach['tool'])
                <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-5">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17H9.75m9.75 0a3 3 0 11-6 0 3 3 0 016 0zM3.75 13.5h.75M3.75 9.75h.75M3.75 6h.75M9.75 6h.008v.008H9.75V6zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 21h.75m9.75-21h.008v.008H13.5V0zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        <h3 class="font-semibold text-blue-900">{{ __('Recommended tool') }}</h3>
                    </div>
                    <p class="mt-2 text-sm font-medium text-blue-800">{{ $allocoreCoach['tool']['name'] }}</p>
                    <p class="mt-1 text-sm text-blue-700">{{ $allocoreCoach['tool']['guide'] }}</p>
                    <div class="mt-3">
                        @if ($allocoreCoach['tool']['subscribed'])
                            <a href="{{ $allocoreCoach['tool']['route'] }}" class="inline-flex items-center rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                                {{ __('Open') }} {{ $allocoreCoach['tool']['name'] }}
                                <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('billing.plans', ['module' => $allocoreCoach['tool']['key']]) }}" class="inline-flex items-center rounded-lg border border-[#ff9200] bg-white px-4 py-2 text-sm font-semibold text-[#ff9200] hover:bg-orange-50">
                                {{ __('Add') }} {{ $allocoreCoach['tool']['name'] }}
                                <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Knowledge clue --}}
            @if ($allocoreCoach['knowledge'])
                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-5">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.995 8.995 0 016 18c3.47 0 6.61-1.44 8.883-3.75l.617-.675V3.758A9.026 9.026 0 0018 3.75c1.052 0 2.062.18 3 .512v14.25A8.994 8.994 0 0118 21c-3.47 0-6.61-1.44-8.883-3.75l-.617-.675V3.758A9.026 9.026 0 0012 6.042z"/></svg>
                        <h3 class="font-semibold text-amber-900">{{ __('Knowledge library clue') }}</h3>
                        @if ($allocoreCoach['knowledge']['is_beginner_friendly'])
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Easy') }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm font-medium text-amber-800">{{ $allocoreCoach['knowledge']['term'] }}</p>
                    <p class="mt-1 text-sm text-amber-700">{{ $allocoreCoach['knowledge']['definition'] }}</p>
                    <a href="{{ $allocoreCoach['knowledge']['link'] }}" target="_blank" class="mt-3 inline-flex items-center text-sm font-semibold text-[#0094af] hover:underline">
                        {{ __('Read more') }}
                        <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    </a>
                </div>
            @endif
        </div>

        @if (! empty($allocoreCoach['all']))
            <div class="mt-6 opacity-0 animate-fade-up" style="animation-delay: 180ms">
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ __('All improvement areas') }}</h3>
                            <p class="text-sm text-slate-500">{{ __('Every weak pillar gets a recommended tool and a knowledge article.') }}</p>
                        </div>
                        <span class="badge badge-gray shrink-0">{{ count($allocoreCoach['all']) }}</span>
                    </summary>

                    <div class="mt-4 space-y-4">
                        @foreach ($allocoreCoach['all'] as $item)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-semibold text-slate-900">
                                                {{ __($item['problem']['pillar'] ?? '') }}
                                                <span class="font-normal text-slate-500">({{ $item['problem']['score'] ?? 0 }}/100)</span>
                                            </h4>
                                            @if ($item['benchmark'] ?? null)
                                                @if ($item['benchmark']['worse'])
                                                    <span class="badge badge-red">-{{ abs($item['benchmark']['diff']) }} {{ __('vs similar') }}</span>
                                                @elseif ($item['benchmark']['better'])
                                                    <span class="badge badge-green">+{{ $item['benchmark']['diff'] }} {{ __('vs similar') }}</span>
                                                @else
                                                    <span class="badge badge-gray">{{ $item['benchmark']['average'] }} {{ __('similar average') }}</span>
                                                @endif
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm text-slate-600">{!! $item['problem']['solution'] ?? '' !!}</p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                                        @if ($item['tool'] ?? null)
                                            <a href="{{ $item['tool']['route'] ?? route('tools.index') }}" class="btn btn-primary btn-sm">
                                                {{ __('Open') }} {{ $item['tool']['name'] }}
                                            </a>
                                        @endif
                                        @if ($item['knowledge'] ?? null)
                                            <a href="{{ $item['knowledge']['link'] }}" class="btn btn-secondary btn-sm">
                                                {{ $item['knowledge']['term'] }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            </div>
        @endif
    </div>
@else
    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-slate-600 opacity-0 animate-fade-up" style="animation-delay: 120ms">
        <p class="font-semibold">{{ __('Your Personal Allocore Coach') }}</p>
        <p class="mt-1 text-sm">{{ $allocoreCoach['cta'] ?? __('Complete an audit to get your first recommendation.') }}</p>
        <a href="{{ route('audit.index') }}" class="mt-3 inline-block rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">{{ __('Start audit') }}</a>
    </div>
@endif
