@extends('layouts.shell')

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Site Settings') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Edit public-facing text on the landing page and authentication pages. Leave empty to use defaults.') }}</p>
                </div>

                <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <x-input-label for="site_name" :value="__('Brand / Site name')" />
                            <x-text-input id="site_name" name="site_name" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('site_name', $settings['site_name'])" />
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
                            <x-text-input id="hero_cta_primary_label" name="hero_cta_primary_label" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_cta_primary_label', $settings['hero_cta_primary_label'])" placeholder="Start free" />
                        </div>

                        <div>
                            <x-input-label for="hero_cta_primary_link" :value="__('Hero primary CTA link')" />
                            <x-text-input id="hero_cta_primary_link" name="hero_cta_primary_link" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_cta_primary_link', $settings['hero_cta_primary_link'])" placeholder="/register" />
                        </div>

                        <div>
                            <x-input-label for="hero_cta_secondary_label" :value="__('Hero secondary CTA label')" />
                            <x-text-input id="hero_cta_secondary_label" name="hero_cta_secondary_label" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_cta_secondary_label', $settings['hero_cta_secondary_label'])" placeholder="Log in" />
                        </div>

                        <div>
                            <x-input-label for="hero_cta_secondary_link" :value="__('Hero secondary CTA link')" />
                            <x-text-input id="hero_cta_secondary_link" name="hero_cta_secondary_link" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('hero_cta_secondary_link', $settings['hero_cta_secondary_link'])" placeholder="/login" />
                        </div>

                        <div class="md:col-span-2">
                            <h4 class="mt-2 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Bottom CTA') }}</h4>
                        </div>

                        <div>
                            <x-input-label for="cta_primary_label" :value="__('Bottom primary CTA label')" />
                            <x-text-input id="cta_primary_label" name="cta_primary_label" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_primary_label', $settings['cta_primary_label'])" placeholder="Create free account" />
                        </div>

                        <div>
                            <x-input-label for="cta_primary_link" :value="__('Bottom primary CTA link')" />
                            <x-text-input id="cta_primary_link" name="cta_primary_link" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_primary_link', $settings['cta_primary_link'])" placeholder="/register" />
                        </div>

                        <div>
                            <x-input-label for="cta_secondary_label" :value="__('Bottom secondary CTA label')" />
                            <x-text-input id="cta_secondary_label" name="cta_secondary_label" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_secondary_label', $settings['cta_secondary_label'])" placeholder="View pricing" />
                        </div>

                        <div>
                            <x-input-label for="cta_secondary_link" :value="__('Bottom secondary CTA link')" />
                            <x-text-input id="cta_secondary_link" name="cta_secondary_link" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('cta_secondary_link', $settings['cta_secondary_link'])" placeholder="/billing/plans" />
                        </div>

                        <div class="md:col-span-2">
                            <h4 class="mt-2 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Feature cards') }}</h4>
                        </div>

                        @foreach ([
                            'auth' => 'Central auth',
                            'teams' => 'Team workspaces',
                            'billing' => 'Billing & plans',
                            'analytics' => 'Analytics dashboard',
                        ] as $key => $label)
                            <div>
                                <x-input-label for="feature_{{ $key }}_title" :value="__(':label title', ['label' => $label])" />
                                <x-text-input id="feature_{{ $key }}_title" name="feature_{{ $key }}_title" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('feature_'.$key.'_title', $settings['feature_'.$key.'_title'])" />
                            </div>

                            <div>
                                <x-input-label for="feature_{{ $key }}_desc" :value="__(':label description', ['label' => $label])" />
                                <x-text-input id="feature_{{ $key }}_desc" name="feature_{{ $key }}_desc" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('feature_'.$key.'_desc', $settings['feature_'.$key.'_desc'])" />
                            </div>
                        @endforeach

                        <div class="md:col-span-2">
                            <x-input-label for="footer_text" :value="__('Footer text')" />
                            <x-text-input id="footer_text" name="footer_text" type="text" class="mt-2 block w-full rounded-lg border-slate-300" :value="old('footer_text', $settings['footer_text'])" />
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end">
                        <x-primary-button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                            {{ __('Save settings') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
