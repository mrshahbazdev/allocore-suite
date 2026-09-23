@php $title = __('Umsatzplaner — 5-Schritte-Planungs-Wizard'); @endphp
@extends('layouts.shell')

@section('content')
    @include('revenueplanner::partials.nav')

    <div class="mx-auto max-w-5xl space-y-8" data-no-navigate x-data="{ currentStep: 1 }">
        <!-- Top Header & Quick Actions -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-800 uppercase tracking-wider">
                    ⚡ Guided Financial Wizard
                </span>
                <h1 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl tracking-tight">{{ __('5-Schritte Umsatzplanungs-Wizard') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Vom Kosten-Fundament zum mathematisch gesicherten Neukundentrichter.') }}</p>
            </div>
            @if ($invoiceData['has_data'] ?? false)
                <button type="button" onclick="applyInvoiceMakerData()" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-300 bg-gradient-to-r from-emerald-50 to-teal-50 px-4 py-2.5 text-xs font-bold text-emerald-900 shadow-sm hover:from-emerald-100 hover:to-teal-100 transition-all hover:scale-[1.02]">
                    🔄 {{ __('1-Klick Import aus InvoiceMaker (:count Rechnungen)', ['count' => $invoiceData['invoice_count']]) }}
                </button>
            @endif
        </div>

        <!-- 5-Step Interactive Navigation Tabs / Breadcrumb Bar -->
        <div class="grid grid-cols-5 gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm text-center">
            <button type="button" @click="currentStep = 1" :class="currentStep === 1 ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="flex flex-col items-center justify-center rounded-xl py-2.5 px-2 transition text-xs font-bold">
                <span class="text-[10px] font-semibold opacity-75">Schritt 1</span>
                <span>🎯 Zielumsatz</span>
            </button>
            <button type="button" @click="currentStep = 2" :class="currentStep === 2 ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="flex flex-col items-center justify-center rounded-xl py-2.5 px-2 transition text-xs font-bold">
                <span class="text-[10px] font-semibold opacity-75">Schritt 2</span>
                <span>📊 Ist & Lücke</span>
            </button>
            <button type="button" @click="currentStep = 3" :class="currentStep === 3 ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="flex flex-col items-center justify-center rounded-xl py-2.5 px-2 transition text-xs font-bold">
                <span class="text-[10px] font-semibold opacity-75">Schritt 3</span>
                <span>💎 Bestandskunden</span>
            </button>
            <button type="button" @click="currentStep = 4" :class="currentStep === 4 ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="flex flex-col items-center justify-center rounded-xl py-2.5 px-2 transition text-xs font-bold">
                <span class="text-[10px] font-semibold opacity-75">Schritt 4</span>
                <span>⚡ Neukunden</span>
            </button>
            <button type="button" @click="currentStep = 5" :class="currentStep === 5 ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'" class="flex flex-col items-center justify-center rounded-xl py-2.5 px-2 transition text-xs font-bold">
                <span class="text-[10px] font-semibold opacity-75">Schritt 5</span>
                <span>📈 Phasen & Speichern</span>
            </button>
        </div>

        <form method="POST" action="{{ route('revenueplanner.wizard.save') }}" id="revenueWizardForm" class="space-y-8">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $plan->id ?? '' }}">

            <!-- General Plan Info & Presets -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Plan-Bezeichnung *') }}</label>
                        <input type="text" name="name" value="{{ old('name', $plan->name ?? 'Jahres-Umsatzplan ' . date('Y')) }}" required class="mt-1 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-900 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Planungsjahr *') }}</label>
                        <input type="number" name="fiscal_year" value="{{ old('fiscal_year', $plan->fiscal_year ?? date('Y')) }}" required min="2020" max="2035" class="mt-1 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-900 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Branche / Benchmark-Preset') }}</label>
                        <select name="industry_type" id="industry_type" onchange="applyIndustryPreset(this.value)" class="mt-1 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-900 focus:border-[#ff9200] focus:ring-[#ff9200]">
                            <option value="service" @selected(old('industry_type', $plan->industry_type ?? '') === 'service')>{{ __('Dienstleistung / Beratung (DB ~90-100%)') }}</option>
                            <option value="agency" @selected(old('industry_type', $plan->industry_type ?? '') === 'agency')>{{ __('Agentur / Software (DB ~80-90%)') }}</option>
                            <option value="trade" @selected(old('industry_type', $plan->industry_type ?? '') === 'trade')>{{ __('Handwerk / Bau (DB ~45-60%)') }}</option>
                            <option value="commerce" @selected(old('industry_type', $plan->industry_type ?? '') === 'commerce')>{{ __('Handel / E-Commerce (DB ~30-40%)') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 1: Zielumsatz-Rechner -->
            <div x-show="currentStep === 1" class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-cyan-100 text-base font-black text-cyan-800">1</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ __('Schritt 1: Zielumsatz-Rechner (Kosten & Gewinnziel)') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('Erfassen Sie alle Fixkosten, den Unternehmerlohn und Ihr Gewinnziel.') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="currentStep = 2" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">
                        {{ __('Weiter zu Schritt 2') }} →
                    </button>
                </div>

                <!-- Fixkosten-Raster -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('A. Jährliche Fixkosten-Struktur (€ / Jahr)') }}</h3>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[11px] font-semibold text-slate-600">{{ __('Personal & Gehälter') }}</label>
                            <input type="number" step="100" name="fixed_costs_breakdown[personnel]" id="fc_personnel" value="{{ old('fixed_costs_breakdown.personnel', $plan->fixed_costs_breakdown['personnel'] ?? 0) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[11px] font-semibold text-slate-600">{{ __('Miete & Nebenkosten') }}</label>
                            <input type="number" step="100" name="fixed_costs_breakdown[rent]" id="fc_rent" value="{{ old('fixed_costs_breakdown.rent', $plan->fixed_costs_breakdown['rent'] ?? 0) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[11px] font-semibold text-slate-600">{{ __('Versicherungen & Beiträge') }}</label>
                            <input type="number" step="100" name="fixed_costs_breakdown[insurance]" id="fc_insurance" value="{{ old('fixed_costs_breakdown.insurance', $plan->fixed_costs_breakdown['insurance'] ?? 0) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[11px] font-semibold text-slate-600">{{ __('Fahrzeuge & Mobilität') }}</label>
                            <input type="number" step="100" name="fixed_costs_breakdown[vehicles]" id="fc_vehicles" value="{{ old('fixed_costs_breakdown.vehicles', $plan->fixed_costs_breakdown['vehicles'] ?? 0) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[11px] font-semibold text-slate-600">{{ __('Software & IT-Lizenzen') }}</label>
                            <input type="number" step="100" name="fixed_costs_breakdown[software]" id="fc_software" value="{{ old('fixed_costs_breakdown.software', $plan->fixed_costs_breakdown['software'] ?? 0) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[11px] font-semibold text-slate-600">{{ __('Telefon & Internet') }}</label>
                            <input type="number" step="100" name="fixed_costs_breakdown[telecom]" id="fc_telecom" value="{{ old('fixed_costs_breakdown.telecom', $plan->fixed_costs_breakdown['telecom'] ?? 0) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[11px] font-semibold text-slate-600">{{ __('Marketing & Werbung') }}</label>
                            <input type="number" step="100" name="fixed_costs_breakdown[marketing]" id="fc_marketing" value="{{ old('fixed_costs_breakdown.marketing', $plan->fixed_costs_breakdown['marketing'] ?? 0) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                            <label class="text-[11px] font-semibold text-slate-600">{{ __('Finanzierung & Zinsen') }}</label>
                            <input type="number" step="100" name="fixed_costs_breakdown[financing]" id="fc_financing" value="{{ old('fixed_costs_breakdown.financing', $plan->fixed_costs_breakdown['financing'] ?? 0) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                        </div>
                    </div>
                </div>

                <!-- Unternehmerlohn & Gewinnziel & Marge -->
                <div class="grid gap-6 md:grid-cols-2 pt-2 border-t border-slate-100">
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('B. Unternehmerlohn (€ / Jahr)') }}</h3>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="text-[10px] font-semibold text-slate-600">{{ __('Netto-Lebensunterhalt') }}</label>
                                <input type="number" step="100" name="owner_compensation_breakdown[net_living]" id="oc_living" value="{{ old('owner_compensation_breakdown.net_living', $plan->owner_compensation_breakdown['net_living'] ?? 60000) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold text-slate-600">{{ __('Vorsorge & KV') }}</label>
                                <input type="number" step="100" name="owner_compensation_breakdown[pension_health]" id="oc_pension" value="{{ old('owner_compensation_breakdown.pension_health', $plan->owner_compensation_breakdown['pension_health'] ?? 15000) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold text-slate-600">{{ __('Steuer-Puffer (~30%)') }}</label>
                                <input type="number" step="100" name="owner_compensation_breakdown[tax_buffer]" id="oc_tax" value="{{ old('owner_compensation_breakdown.tax_buffer', $plan->owner_compensation_breakdown['tax_buffer'] ?? 25000) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('C. Gewinnziel & Deckungsbeitrag') }}</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] font-semibold text-slate-600">{{ __('Gewinnziel / Rücklagen (€/J.)') }}</label>
                                <input type="number" step="500" name="profit_target_annual" id="profit_target_annual" value="{{ old('profit_target_annual', $plan->profit_target_annual ?? 30000) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="text-[11px] font-semibold text-slate-600">{{ __('Deckungsbeitragsmarge (%)') }}</label>
                                <input type="number" step="1" min="1" max="100" name="contribution_margin_percent" id="contribution_margin_percent" value="{{ old('contribution_margin_percent', $plan->contribution_margin_percent ?? 100) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-xl border-slate-300 text-xs font-bold text-cyan-800">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Computed Target Card -->
                <div class="rounded-2xl border border-cyan-200 bg-gradient-to-r from-cyan-50 to-blue-50 p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                    <div>
                        <span class="text-xs font-bold text-cyan-900 uppercase tracking-wider">🎯 {{ __('Errechneter Mindest-Zielumsatz:') }}</span>
                        <div class="text-3xl font-black text-cyan-950" id="display_target_annual">0 € / Jahr</div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-cyan-800">{{ __('Monatlicher Zielumsatz:') }}</span>
                        <div class="text-xl font-bold text-cyan-900" id="display_target_monthly">0 € / Mon.</div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Ist-Umsatz & Umsatzlücke -->
            <div x-show="currentStep === 2" class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-indigo-100 text-base font-black text-indigo-800">2</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ __('Schritt 2: Ist-Umsatz & Umsatzlücken-Radar') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('Geben Sie Ihren aktuellen Jahresumsatz ein, um die Finanzierungslücke zu ermitteln.') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="currentStep = 1" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">← {{ __('Zurück') }}</button>
                        <button type="button" @click="currentStep = 3" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">{{ __('Weiter zu Schritt 3') }} →</button>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2 items-center">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Aktueller Jahres-Ist-Umsatz (€ / Jahr) *') }}</label>
                        <input type="number" step="1000" name="actual_revenue_annual" id="actual_revenue_annual" value="{{ old('actual_revenue_annual', $plan->actual_revenue_annual ?? 0) }}" oninput="recalc()" required class="calc-input mt-1 w-full rounded-2xl border-slate-300 text-lg font-black text-slate-900 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <p class="text-[11px] text-slate-500 mt-1.5">Tragen Sie Ihren bisherigen Umsatz ein oder nutzen Sie den 1-Klick-Import aus InvoiceMaker.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-5 bg-gradient-to-br from-slate-50 to-rose-50/40 shadow-sm" id="gap_card">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">{{ __('Umsatzlücke:') }}</span>
                            <span class="text-2xl font-black text-rose-600" id="display_gap_annual">0 €</span>
                        </div>
                        <p class="text-xs font-semibold text-slate-600 mt-2" id="display_coverage">Zielerreichung: 0%</p>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Bestandskunden-Hebel -->
            <div x-show="currentStep === 3" class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-100 text-base font-black text-amber-800">3</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ __('Schritt 3: Bestandskunden-Potenzial (Lücke ohne Werbekosten schließen)') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('Optimieren Sie Preise, Wiederkauf und Zusatzleistungen bei bestehenden Kunden.') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="currentStep = 2" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">← {{ __('Zurück') }}</button>
                        <button type="button" @click="currentStep = 4" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">{{ __('Weiter zu Schritt 4') }} →</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <label class="text-[11px] font-semibold text-slate-600">{{ __('Preiserhöhung (%)') }}</label>
                        <input type="number" step="0.5" min="0" max="50" name="existing_customer_levers[price_increase_percent]" id="lev_price" value="{{ old('existing_customer_levers.price_increase_percent', $plan->existing_customer_levers['price_increase_percent'] ?? 5) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <label class="text-[11px] font-semibold text-slate-600">{{ __('Mehrfacheinkauf (%)') }}</label>
                        <input type="number" step="0.5" min="0" max="50" name="existing_customer_levers[repeat_purchase_increase_percent]" id="lev_repeat" value="{{ old('existing_customer_levers.repeat_purchase_increase_percent', $plan->existing_customer_levers['repeat_purchase_increase_percent'] ?? 5) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <label class="text-[11px] font-semibold text-slate-600">{{ __('Cross-Selling (€/J.)') }}</label>
                        <input type="number" step="500" min="0" name="existing_customer_levers[cross_selling_revenue]" id="lev_cross" value="{{ old('existing_customer_levers.cross_selling_revenue', $plan->existing_customer_levers['cross_selling_revenue'] ?? 10000) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <label class="text-[11px] font-semibold text-slate-600">{{ __('Churn-Senkung (€/J.)') }}</label>
                        <input type="number" step="500" min="0" name="existing_customer_levers[churn_reduction_revenue]" id="lev_churn" value="{{ old('existing_customer_levers.churn_reduction_revenue', $plan->existing_customer_levers['churn_reduction_revenue'] ?? 5000) }}" oninput="recalc()" class="calc-input mt-1 w-full rounded-lg border-slate-300 text-xs font-semibold">
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                    <div>
                        <span class="text-xs font-bold text-amber-900 uppercase tracking-wider">💰 {{ __('Bestandskunden-Ertragspotenzial:') }}</span>
                        <div class="text-2xl font-black text-amber-950" id="display_existing_potential">+0 €</div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold text-slate-700">{{ __('Verbleibende Restlücke für Neukunden:') }}</span>
                        <div class="text-xl font-bold text-rose-700" id="display_remaining_gap">0 €</div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Neukunden-Bedarf -->
            <div x-show="currentStep === 4" class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-purple-100 text-base font-black text-purple-800">4</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ __('Schritt 4: Neukunden-Bedarf & Vertriebs-Trichter') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('Berechnen Sie den exakten Lead- und Abschlussbedarf zur vollständigen Schließung der Restlücke.') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="currentStep = 3" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">← {{ __('Zurück') }}</button>
                        <button type="button" @click="currentStep = 5" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">{{ __('Weiter zu Schritt 5') }} →</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Ø Auftragswert / Deal-Größe (€) *') }}</label>
                        <input type="number" step="100" min="10" name="average_deal_size" id="average_deal_size" value="{{ old('average_deal_size', $plan->average_deal_size ?? 2500) }}" oninput="recalc()" required class="calc-input mt-1 w-full rounded-xl border-slate-300 text-sm font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Abschlussquote (Lead-zu-Kunde in %) *') }}</label>
                        <input type="number" step="1" min="1" max="100" name="lead_to_close_rate_percent" id="lead_to_close_rate_percent" value="{{ old('lead_to_close_rate_percent', $plan->lead_to_close_rate_percent ?? 20) }}" oninput="recalc()" required class="calc-input mt-1 w-full rounded-xl border-slate-300 text-sm font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                </div>

                <!-- Funnel Result Card -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 rounded-2xl border border-purple-200 bg-gradient-to-br from-purple-50 via-indigo-50/40 to-cyan-50 p-5 text-center shadow-sm">
                    <div class="rounded-xl bg-white/70 p-3 shadow-xs">
                        <p class="text-[11px] font-bold text-purple-900 uppercase">{{ __('Neukunden / Jahr') }}</p>
                        <p class="mt-1 text-2xl font-black text-purple-950" id="display_deals_annual">0</p>
                    </div>
                    <div class="rounded-xl bg-white/70 p-3 shadow-xs">
                        <p class="text-[11px] font-bold text-purple-900 uppercase">{{ __('Neukunden / Monat') }}</p>
                        <p class="mt-1 text-2xl font-black text-purple-950" id="display_deals_monthly">0</p>
                    </div>
                    <div class="rounded-xl bg-white/70 p-3 shadow-xs">
                        <p class="text-[11px] font-bold text-cyan-900 uppercase">{{ __('Leads / Jahr') }}</p>
                        <p class="mt-1 text-2xl font-black text-cyan-950" id="display_leads_annual">0</p>
                    </div>
                    <div class="rounded-xl bg-white/70 p-3 shadow-xs">
                        <p class="text-[11px] font-bold text-cyan-900 uppercase">{{ __('Leads / Monat') }}</p>
                        <p class="mt-1 text-2xl font-black text-cyan-950" id="display_leads_monthly">0</p>
                    </div>
                </div>
            </div>

            <!-- STEP 5: Saisonalität & Speichern -->
            <div x-show="currentStep === 5" class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-100 text-base font-black text-emerald-800">5</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ __('Schritt 5: Saisonalitäts-Verteilung & Abschluss') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('Wählen Sie das saisonale Verlaufsmuster für eine realistische 12-Monats-Phasierung.') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="currentStep = 4" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">← {{ __('Zurück') }}</button>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Saisonalitäts-Muster') }}</label>
                        <select name="seasonality_pattern" id="seasonality_pattern" class="mt-1 w-full rounded-xl border-slate-300 text-sm font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                            <option value="even" @selected(old('seasonality_pattern', $plan->seasonality_pattern ?? '') === 'even')>{{ __('Gleichmäßig (jeder Monat 8,3%)') }}</option>
                            <option value="summer_dip" @selected(old('seasonality_pattern', $plan->seasonality_pattern ?? '') === 'summer_dip')>{{ __('Sommerloch (Juli/August schwächer, Herbst stärker)') }}</option>
                            <option value="q4_peak" @selected(old('seasonality_pattern', $plan->seasonality_pattern ?? '') === 'q4_peak')>{{ __('Q4-Peak (Jahresendgeschäft / Weihnachten)') }}</option>
                            <option value="b2b_service" @selected(old('seasonality_pattern', $plan->seasonality_pattern ?? '') === 'b2b_service')>{{ __('Klassischer B2B-Verlauf (Q1 & Q4 stark)') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Strategische Notizen') }}</label>
                        <textarea name="notes" rows="3" placeholder="z. B. Fokus im Q1 auf Stundensatzerhöhung und Empfehlungsmarketing..." class="mt-1 w-full rounded-xl border-slate-300 text-xs font-normal">{{ old('notes', $plan->notes ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100">
                    <a href="{{ route('revenueplanner.dashboard') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                        {{ __('Abbrechen') }}
                    </a>
                    <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700 transition-all hover:scale-[1.02]">
                        💾 {{ __('Umsatzplan berechnen & aktivieren') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Live Calculation JS -->
    <script>
        const invoiceData = @json($invoiceData ?? []);

        function applyInvoiceMakerData() {
            if (!invoiceData || !invoiceData.has_data) return;
            if (invoiceData.actual_revenue_annual > 0) {
                document.getElementById('actual_revenue_annual').value = invoiceData.actual_revenue_annual;
            }
            if (invoiceData.fixed_costs_breakdown) {
                for (const [k, v] of Object.entries(invoiceData.fixed_costs_breakdown)) {
                    const el = document.getElementById('fc_' + k);
                    if (el && v > 0) el.value = v;
                }
            }
            recalc();
        }

        function applyIndustryPreset(type) {
            const marginEl = document.getElementById('contribution_margin_percent');
            if (type === 'service') marginEl.value = 100;
            else if (type === 'agency') marginEl.value = 85;
            else if (type === 'trade') marginEl.value = 55;
            else if (type === 'commerce') marginEl.value = 35;
            recalc();
        }

        function recalc() {
            // Fixkosten
            let fixedCosts = 0;
            ['personnel', 'rent', 'insurance', 'vehicles', 'software', 'telecom', 'marketing', 'financing'].forEach(id => {
                fixedCosts += parseFloat(document.getElementById('fc_' + id)?.value || 0);
            });

            // Inhaberlohn
            const living = parseFloat(document.getElementById('oc_living')?.value || 0);
            const pension = parseFloat(document.getElementById('oc_pension')?.value || 0);
            const tax = parseFloat(document.getElementById('oc_tax')?.value || 0);
            const ownerComp = living + pension + tax;

            // Gewinn & Marge
            const profit = parseFloat(document.getElementById('profit_target_annual')?.value || 0);
            let margin = parseFloat(document.getElementById('contribution_margin_percent')?.value || 100);
            if (margin <= 0 || margin > 100) margin = 100;

            const targetAnnual = (fixedCosts + ownerComp + profit) / (margin / 100);
            const targetMonthly = targetAnnual / 12;

            document.getElementById('display_target_annual').innerText = Math.round(targetAnnual).toLocaleString('de-DE') + ' € / Jahr';
            document.getElementById('display_target_monthly').innerText = Math.round(targetMonthly).toLocaleString('de-DE') + ' € / Mon.';

            // Ist & Lücke
            const actualAnnual = parseFloat(document.getElementById('actual_revenue_annual')?.value || 0);
            const gapAnnual = Math.max(0, targetAnnual - actualAnnual);
            const coverage = targetAnnual > 0 ? Math.round((actualAnnual / targetAnnual) * 100) : 100;

            document.getElementById('display_gap_annual').innerText = Math.round(gapAnnual).toLocaleString('de-DE') + ' €';
            document.getElementById('display_coverage').innerText = 'Zielerreichung: ' + coverage + '%';

            // Bestandskunden-Hebel
            const pInc = parseFloat(document.getElementById('lev_price')?.value || 0);
            const rInc = parseFloat(document.getElementById('lev_repeat')?.value || 0);
            const cross = parseFloat(document.getElementById('lev_cross')?.value || 0);
            const churn = parseFloat(document.getElementById('lev_churn')?.value || 0);

            const existingPotential = (actualAnnual * (pInc / 100)) + (actualAnnual * (rInc / 100)) + cross + churn;
            const remainingGap = Math.max(0, gapAnnual - existingPotential);

            document.getElementById('display_existing_potential').innerText = '+' + Math.round(existingPotential).toLocaleString('de-DE') + ' €';
            document.getElementById('display_remaining_gap').innerText = Math.round(remainingGap).toLocaleString('de-DE') + ' €';

            // Neukunden-Bedarf
            const dealSize = parseFloat(document.getElementById('average_deal_size')?.value || 2500) || 2500;
            const closeRate = (parseFloat(document.getElementById('lead_to_close_rate_percent')?.value || 20) || 20) / 100;

            const wonDealsAnnual = Math.ceil(remainingGap / dealSize);
            const wonDealsMonthly = Math.ceil(wonDealsAnnual / 12);

            const leadsAnnual = Math.ceil(wonDealsAnnual / closeRate);
            const leadsMonthly = Math.ceil(leadsAnnual / 12);

            document.getElementById('display_deals_annual').innerText = wonDealsAnnual;
            document.getElementById('display_deals_monthly').innerText = wonDealsMonthly;
            document.getElementById('display_leads_annual').innerText = leadsAnnual;
            document.getElementById('display_leads_monthly').innerText = leadsMonthly;
        }

        document.addEventListener('DOMContentLoaded', recalc);
    </script>
@endsection
