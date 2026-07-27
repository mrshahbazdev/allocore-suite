@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Setup Wizard') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Get your platform ready in a few simple steps.') }}</p>
    </div>

    <div class="mb-6 flex items-center gap-2">
        @foreach (['Site', 'Modules', 'Appearance', 'Done'] as $i => $label)
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold {{ $step >= $i ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $i + 1 }}</span>
                <span class="text-sm font-medium {{ $step >= $i ? 'text-slate-900' : 'text-slate-500' }}">{{ __($label) }}</span>
            </div>
            @if (! $loop->last)
                <span class="mx-2 h-px w-8 bg-slate-300"></span>
            @endif
        @endforeach
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @if ($step == 0)
            <form method="POST" action="{{ route('admin.setup.site') }}" class="max-w-xl space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Site name') }}</label>
                    <input name="site_name" value="{{ old('site_name', $settings['site_name']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Support email') }}</label>
                    <input name="support_email" type="email" value="{{ old('support_email', $settings['support_email']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Default locale') }}</label>
                    <select name="default_locale" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                        @foreach (config('app.available_locales', ['en']) as $locale)
                            <option value="{{ $locale }}" {{ old('default_locale', $settings['default_locale']) === $locale ? 'selected' : '' }}>{{ config('app.locale_names.'.$locale, strtoupper($locale)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Continue') }}</button>
            </form>
        @elseif ($step == 1)
            <form method="POST" action="{{ route('admin.setup.modules') }}">
                @csrf
                <p class="mb-4 text-sm text-slate-500">{{ __('Choose the tools you want to activate right now. You can change this later from Modules.') }}</p>
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach ($modules as $module)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                            <input type="checkbox" name="modules[]" value="{{ $module->id }}" {{ $module->is_active ? 'checked' : '' }} class="mt-1 rounded border-slate-300 text-indigo-600">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $module->name }}</div>
                                <div class="text-sm text-slate-500">{{ $module->description }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
                <div class="mt-6">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Continue') }}</button>
                </div>
            </form>
        @elseif ($step == 2)
            <form method="POST" action="{{ route('admin.setup.appearance') }}" class="max-w-xl space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Logo URL') }}</label>
                    <input name="site_logo" value="{{ old('site_logo', $settings['site_logo']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Favicon URL') }}</label>
                    <input name="site_favicon" value="{{ old('site_favicon', $settings['site_favicon']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Primary color') }}</label>
                        <input type="color" name="primary_color" value="{{ old('primary_color', $settings['primary_color']) }}" class="mt-1 h-10 w-full rounded-lg border-slate-300 p-1">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Accent color') }}</label>
                        <input type="color" name="accent_color" value="{{ old('accent_color', $settings['accent_color']) }}" class="mt-1 h-10 w-full rounded-lg border-slate-300 p-1">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Footer text') }}</label>
                    <input name="footer_text" value="{{ old('footer_text', $settings['footer_text']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Continue') }}</button>
            </form>
        @else
            <div class="text-center py-10">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </div>
                <h2 class="mt-4 text-xl font-bold text-slate-900">{{ __('You are all set!') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ __('Start using your dashboard or customize more from Appearance.') }}</p>
                <form method="POST" action="{{ route('admin.setup.complete') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Go to dashboard') }}</button>
                </form>
            </div>
        @endif
    </div>
@endsection
