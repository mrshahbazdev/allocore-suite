<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-base">📈</span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">{{ __('Allocore Umsatzplaner') }}</h3>
        </div>
        <a href="{{ route('revenueplanner.dashboard') }}" class="text-xs font-semibold text-[#0094af] hover:underline">
            {{ __('Plan öffnen') }} &rarr;
        </a>
    </div>

    @if ($activePlan)
        <div class="space-y-2 pt-1">
            <div class="flex items-baseline justify-between text-xs">
                <span class="text-slate-500">{{ __('Jahres-Zielumsatz:') }}</span>
                <span class="font-bold text-slate-900">{{ number_format($activePlan->target_revenue_annual, 0, ',', '.') }} €</span>
            </div>
            <div class="flex items-baseline justify-between text-xs">
                <span class="text-slate-500">{{ __('Ist-Umsatz:') }}</span>
                <span class="font-bold text-emerald-600">{{ number_format($activePlan->actual_revenue_annual, 0, ',', '.') }} €</span>
            </div>
            <!-- Progress Bar -->
            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-gradient-to-r from-[#0094af] to-emerald-500" style="width: {{ min(100, $activePlan->coverage_ratio_percent) }}%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                <span>{{ $activePlan->coverage_ratio_percent }}% {{ __('erreicht') }}</span>
                <span>{{ __('Lücke:') }} {{ number_format($activePlan->revenue_gap_annual, 0, ',', '.') }} €</span>
            </div>
        </div>
    @else
        <p class="text-xs text-slate-500">{{ __('Noch kein Umsatzplan berechnet.') }}</p>
        <a href="{{ route('revenueplanner.wizard') }}" class="inline-block rounded-xl bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-700 hover:bg-orange-100">
            ⚡ {{ __('Zielumsatz berechnen') }}
        </a>
    @endif
</div>
