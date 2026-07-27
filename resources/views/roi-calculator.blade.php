@extends('layouts.public')

@section('title', __('ROI Rechner'))
@section('meta_description', __('Berechnen Sie Ihr Umsatzpotenzial, Ihre Zeitersparnis und Ihre Gewinnsteigerung mit Allocore.'))

@section('content')
    <section class="py-16 lg:py-24">
        <div class="mx-auto max-w-4xl px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">{{ __('ROI Calculator') }}</p>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">{{ __('Was kostet ineffizientes Wachstum?') }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">{{ __('Berechnen Sie Umsatzpotenzial, Zeitersparnis und Gewinnsteigerung für Ihr Unternehmen.') }}</p>
            </div>

            <div class="mt-12 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <form method="POST" action="{{ route('roi-calculator.index') }}" class="grid gap-6 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="employees" class="block text-sm font-medium text-slate-700">{{ __('Mitarbeiter') }}</label>
                        <input type="number" name="employees" id="employees" value="{{ old('employees', $input['employees'] ?? 10) }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="hourly_rate" class="block text-sm font-medium text-slate-700">{{ __('Stundensatz (EUR)') }}</label>
                        <input type="number" step="0.01" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $input['hourly_rate'] ?? 75) }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="hours_saved_per_week" class="block text-sm font-medium text-slate-700">{{ __('Stunden Ersparnis / Mitarbeiter / Woche') }}</label>
                        <input type="number" step="0.1" name="hours_saved_per_week" id="hours_saved_per_week" value="{{ old('hours_saved_per_week', $input['hours_saved_per_week'] ?? 2) }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="annual_revenue" class="block text-sm font-medium text-slate-700">{{ __('Aktueller Jahresumsatz (EUR)') }}</label>
                        <input type="number" step="0.01" name="annual_revenue" id="annual_revenue" value="{{ old('annual_revenue', $input['annual_revenue'] ?? 1000000) }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="revenue_growth_percent" class="block text-sm font-medium text-slate-700">{{ __('Erwartetes Umsatzwachstum durch bessere Prozesse (%)') }}</label>
                        <input type="number" step="0.1" name="revenue_growth_percent" id="revenue_growth_percent" value="{{ old('revenue_growth_percent', $input['revenue_growth_percent'] ?? 10) }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div class="sm:col-span-2 flex items-center justify-end">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white hover:bg-indigo-700">{{ __('ROI berechnen') }}</button>
                    </div>
                </form>

                @if ($result)
                    <div class="mt-10 border-t border-slate-200 pt-10">
                        <h2 class="text-xl font-bold text-slate-900">{{ __('Ihr jährliches Potenzial') }}</h2>
                        <div class="mt-6 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-5 text-center">
                                <p class="text-sm text-slate-500">{{ __('Zeitersparnis') }}</p>
                                <p class="mt-2 text-3xl font-extrabold text-slate-900">€{{ number_format($result['time_savings'], 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-5 text-center">
                                <p class="text-sm text-slate-500">{{ __('Umsatzpotenzial') }}</p>
                                <p class="mt-2 text-3xl font-extrabold text-slate-900">€{{ number_format($result['revenue_potential'], 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-5 text-center">
                                <p class="text-sm text-slate-500">{{ __('Gewinnsteigerung') }}</p>
                                <p class="mt-2 text-3xl font-extrabold text-slate-900">€{{ number_format($result['profit_lift'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="mt-6 rounded-xl bg-indigo-600 p-6 text-center text-white">
                            <p class="text-sm font-medium opacity-90">{{ __('Geschätztes jährliches Nutzenpotenzial') }}</p>
                            <p class="mt-2 text-4xl font-extrabold">€{{ number_format($result['total_benefit'], 0, ',', '.') }}</p>
                        </div>
                        <div class="mt-8 text-center">
                            <a href="{{ route('audit-example.index') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-6 py-3 text-base font-semibold text-white hover:bg-slate-700">{{ __('Kostenlosen Audit starten') }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
