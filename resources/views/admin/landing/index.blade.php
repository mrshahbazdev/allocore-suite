@extends('layouts.shell')

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Landing Page') }}</h1>
                    <p class="text-sm text-slate-500">{{ __('Edit the public home page in a simple form. Leave empty to use translations.') }}</p>
                </div>
                <form method="GET" action="{{ route('admin.landing.index') }}" class="flex items-center gap-2">
                    <label for="landing-locale" class="text-sm font-medium text-slate-700">{{ __('Editing language') }}</label>
                    <select id="landing-locale" name="locale" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (config('app.available_locales', ['en', 'de']) as $l)
                            <option value="{{ $l }}" {{ $locale === $l ? 'selected' : '' }}>
                                {{ config('app.locale_names.'.$l, strtoupper($l)) }}
                                @if ($l === 'de')
                                    ({{ __('baseline') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if ($locale !== 'de')
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    {{ __('You are editing the :locale version. Empty fields will fall back to translations.', ['locale' => config('app.locale_names.'.$locale, strtoupper($locale))]) }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <form method="POST" action="{{ route('admin.landing.update') }}" class="p-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="locale" value="{{ $locale }}">

                    <div class="space-y-10">
                        {{-- Hero --}}
                        <div class="border-b border-slate-200 pb-8">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Hero') }}</h4>
                            <div class="mt-4 grid gap-6 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <x-input-label for="hero_badge" :value="__('Hero badge')" />
                                    <x-text-input id="hero_badge" name="hero_badge" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_badge', $settings['hero_badge'])" />
                                </div>
                                <div>
                                    <x-input-label for="hero_heading" :value="__('Hero heading')" />
                                    <x-text-input id="hero_heading" name="hero_heading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_heading', $settings['hero_heading'])" />
                                </div>
                                <div>
                                    <x-input-label for="hero_subheading" :value="__('Hero subheading')" />
                                    <x-text-input id="hero_subheading" name="hero_subheading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_subheading', $settings['hero_subheading'])" />
                                </div>
                                <div>
                                    <x-input-label for="hero_cta_primary_label" :value="__('Hero primary CTA label')" />
                                    <x-text-input id="hero_cta_primary_label" name="hero_cta_primary_label" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_cta_primary_label', $settings['hero_cta_primary_label'])" />
                                </div>
                                <div>
                                    <x-input-label for="hero_cta_primary_link" :value="__('Hero primary CTA link')" />
                                    <x-text-input id="hero_cta_primary_link" name="hero_cta_primary_link" type="url" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_cta_primary_link', $settings['hero_cta_primary_link'])" />
                                </div>
                                <div>
                                    <x-input-label for="hero_cta_secondary_label" :value="__('Hero secondary CTA label')" />
                                    <x-text-input id="hero_cta_secondary_label" name="hero_cta_secondary_label" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_cta_secondary_label', $settings['hero_cta_secondary_label'])" />
                                </div>
                                <div>
                                    <x-input-label for="hero_cta_secondary_link" :value="__('Hero secondary CTA link')" />
                                    <x-text-input id="hero_cta_secondary_link" name="hero_cta_secondary_link" type="url" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_cta_secondary_link', $settings['hero_cta_secondary_link'])" />
                                </div>
                            </div>
                        </div>

                        {{-- Top stats --}}
                        <div class="border-b border-slate-200 pb-8">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Top stats') }}</h4>
                            <p class="text-xs text-slate-500">{{ __('Labels + values under the hero. Leave empty to use defaults.') }}</p>
                            <div class="mt-4 space-y-4">
                                @foreach (range(0, 2) as $i)
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div>
                                            <x-input-label for="top_stats_{{ $i }}_label" :value="__('Stat :num label', ['num' => $i + 1])" />
                                            <x-text-input id="top_stats_{{ $i }}_label" name="top_stats[{{ $i }}][label]" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('top_stats.'.$i.'.label', $settings['top_stats'][$i]['label'] ?? '')" />
                                        </div>
                                        <div>
                                            <x-input-label for="top_stats_{{ $i }}_value" :value="__('Stat :num value', ['num' => $i + 1])" />
                                            <x-text-input id="top_stats_{{ $i }}_value" name="top_stats[{{ $i }}][value]" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('top_stats.'.$i.'.value', $settings['top_stats'][$i]['value'] ?? '')" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Framework --}}
                        <div class="border-b border-slate-200 pb-8">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Allocore Framework') }}</h4>
                            <div class="mt-4 grid gap-6 md:grid-cols-2">
                                <div>
                                    <x-input-label for="framework_heading" :value="__('Framework heading')" />
                                    <x-text-input id="framework_heading" name="framework_heading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('framework_heading', $settings['framework_heading'])" />
                                </div>
                                <div>
                                    <x-input-label for="framework_subheading" :value="__('Framework subheading')" />
                                    <x-text-input id="framework_subheading" name="framework_subheading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('framework_subheading', $settings['framework_subheading'])" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="framework_description" :value="__('Framework description')" />
                                    <textarea id="framework_description" name="framework_description" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('framework_description', $settings['framework_description']) }}</textarea>
                                </div>
                            </div>
                            <div class="mt-6 space-y-4">
                                @foreach (range(0, 5) as $i)
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div>
                                            <x-input-label for="framework_steps_{{ $i }}_title" :value="__('Step :num title', ['num' => $i + 1])" />
                                            <x-text-input id="framework_steps_{{ $i }}_title" name="framework_steps[{{ $i }}][title]" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('framework_steps.'.$i.'.title', $settings['framework_steps'][$i]['title'] ?? '')" />
                                        </div>
                                        <div>
                                            <x-input-label for="framework_steps_{{ $i }}_desc" :value="__('Step :num description', ['num' => $i + 1])" />
                                            <x-text-input id="framework_steps_{{ $i }}_desc" name="framework_steps[{{ $i }}][desc]" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('framework_steps.'.$i.'.desc', $settings['framework_steps'][$i]['desc'] ?? '')" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Features --}}
                        <div class="border-b border-slate-200 pb-8">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Features') }}</h4>
                            <div class="mt-4 grid gap-6 md:grid-cols-2">
                                <div>
                                    <x-input-label for="features_heading" :value="__('Features heading')" />
                                    <x-text-input id="features_heading" name="features_heading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('features_heading', $settings['features_heading'])" />
                                </div>
                                <div>
                                    <x-input-label for="features_subheading" :value="__('Features subheading')" />
                                    <x-text-input id="features_subheading" name="features_subheading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('features_subheading', $settings['features_subheading'])" />
                                </div>
                            </div>
                            <div class="mt-6 grid gap-6 md:grid-cols-2">
                                @foreach (['auth' => 'Central auth', 'teams' => 'Team workspaces', 'billing' => 'Billing & plans', 'analytics' => 'Analytics dashboard'] as $key => $label)
                                    <div>
                                        <x-input-label for="feature_{{ $key }}_title" :value="__('Feature :label title', ['label' => $label])" />
                                        <x-text-input id="feature_{{ $key }}_title" name="feature_{{ $key }}_title" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('feature_'.$key.'_title', $settings['feature_'.$key.'_title'])" placeholder="{{ $label }} title" />
                                        <x-input-label for="feature_{{ $key }}_desc" class="mt-3" :value="__('Feature :label description', ['label' => $label])" />
                                        <x-text-input id="feature_{{ $key }}_desc" name="feature_{{ $key }}_desc" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('feature_'.$key.'_desc', $settings['feature_'.$key.'_desc'])" placeholder="{{ $label }} description" />
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- How it works --}}
                        <div class="border-b border-slate-200 pb-8">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('How it works') }}</h4>
                            <div class="mt-4 grid gap-6 md:grid-cols-2">
                                <div>
                                    <x-input-label for="how_heading" :value="__('How it works heading')" />
                                    <x-text-input id="how_heading" name="how_heading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('how_heading', $settings['how_heading'])" />
                                </div>
                                <div>
                                    <x-input-label for="how_subheading" :value="__('How it works subheading')" />
                                    <x-text-input id="how_subheading" name="how_subheading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('how_subheading', $settings['how_subheading'])" />
                                </div>
                            </div>
                            <div class="mt-6 space-y-4">
                                @foreach (range(0, 2) as $i)
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div>
                                            <x-input-label for="how_steps_{{ $i }}_title" :value="__('Step :num title', ['num' => $i + 1])" />
                                            <x-text-input id="how_steps_{{ $i }}_title" name="how_steps[{{ $i }}][title]" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('how_steps.'.$i.'.title', $settings['how_steps'][$i]['title'] ?? '')" />
                                        </div>
                                        <div>
                                            <x-input-label for="how_steps_{{ $i }}_desc" :value="__('Step :num description', ['num' => $i + 1])" />
                                            <x-text-input id="how_steps_{{ $i }}_desc" name="how_steps[{{ $i }}][desc]" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('how_steps.'.$i.'.desc', $settings['how_steps'][$i]['desc'] ?? '')" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Modules --}}
                        <div class="border-b border-slate-200 pb-8">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Modules') }}</h4>
                            <div class="mt-4 grid gap-6 md:grid-cols-2">
                                <div>
                                    <x-input-label for="modules_heading" :value="__('Modules heading')" />
                                    <x-text-input id="modules_heading" name="modules_heading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('modules_heading', $settings['modules_heading'])" />
                                </div>
                                <div>
                                    <x-input-label for="modules_subheading" :value="__('Modules subheading')" />
                                    <x-text-input id="modules_subheading" name="modules_subheading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('modules_subheading', $settings['modules_subheading'])" />
                                </div>
                            </div>
                        </div>

                        {{-- Testimonial --}}
                        <div class="border-b border-slate-200 pb-8">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Testimonial') }}</h4>
                            <div class="mt-4 grid gap-6 md:grid-cols-2">
                                <div>
                                    <x-input-label for="testimonials_heading" :value="__('Testimonial heading')" />
                                    <x-text-input id="testimonials_heading" name="testimonials_heading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('testimonials_heading', $settings['testimonials_heading'])" />
                                </div>
                                <div>
                                    <x-input-label for="testimonials_author" :value="__('Testimonial author')" />
                                    <x-text-input id="testimonials_author" name="testimonials_author" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('testimonials_author', $settings['testimonials_author'])" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="testimonials_quote" :value="__('Testimonial quote')" />
                                    <textarea id="testimonials_quote" name="testimonials_quote" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('testimonials_quote', $settings['testimonials_quote']) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Bottom CTA --}}
                        <div class="border-b border-slate-200 pb-8">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Bottom call to action') }}</h4>
                            <div class="mt-4 grid gap-6 md:grid-cols-2">
                                <div>
                                    <x-input-label for="cta_heading" :value="__('CTA heading')" />
                                    <x-text-input id="cta_heading" name="cta_heading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_heading', $settings['cta_heading'])" />
                                </div>
                                <div>
                                    <x-input-label for="cta_subheading" :value="__('CTA subheading')" />
                                    <x-text-input id="cta_subheading" name="cta_subheading" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_subheading', $settings['cta_subheading'])" />
                                </div>
                                <div>
                                    <x-input-label for="cta_primary_label" :value="__('Primary CTA label')" />
                                    <x-text-input id="cta_primary_label" name="cta_primary_label" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_primary_label', $settings['cta_primary_label'])" />
                                </div>
                                <div>
                                    <x-input-label for="cta_primary_link" :value="__('Primary CTA link')" />
                                    <x-text-input id="cta_primary_link" name="cta_primary_link" type="url" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_primary_link', $settings['cta_primary_link'])" />
                                </div>
                                <div>
                                    <x-input-label for="cta_secondary_label" :value="__('Secondary CTA label')" />
                                    <x-text-input id="cta_secondary_label" name="cta_secondary_label" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_secondary_label', $settings['cta_secondary_label'])" />
                                </div>
                                <div>
                                    <x-input-label for="cta_secondary_link" :value="__('Secondary CTA link')" />
                                    <x-text-input id="cta_secondary_link" name="cta_secondary_link" type="url" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_secondary_link', $settings['cta_secondary_link'])" />
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div>
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Footer') }}</h4>
                            <div class="mt-4">
                                <x-input-label for="footer_text" :value="__('Footer text')" />
                                <x-text-input id="footer_text" name="footer_text" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('footer_text', $settings['footer_text'])" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end">
                        <x-primary-button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                            {{ __('Save landing page') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
