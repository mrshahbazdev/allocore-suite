@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Appearance') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Customize your site branding, colors, menus and footer like a WordPress theme.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.appearance.update') }}" x-data="{ menu: {{ json_encode(old('public_nav_menu', $settings['public_nav_menu']) ?: []) }}, social: {{ json_encode(old('social_links', $settings['social_links']) ?: []) }} }">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Branding') }}</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Site name') }}</label>
                        <input name="site_name" value="{{ old('site_name', $settings['site_name']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Font family') }}</label>
                        <select name="font_family" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                            <option value="figtree" {{ old('font_family', $settings['font_family']) === 'figtree' ? 'selected' : '' }}>Figtree</option>
                            <option value="inter" {{ old('font_family', $settings['font_family']) === 'inter' ? 'selected' : '' }}>Inter</option>
                            <option value="roboto" {{ old('font_family', $settings['font_family']) === 'roboto' ? 'selected' : '' }}>Roboto</option>
                            <option value="poppins" {{ old('font_family', $settings['font_family']) === 'poppins' ? 'selected' : '' }}>Poppins</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Logo URL') }}</label>
                        <input name="site_logo" value="{{ old('site_logo', $settings['site_logo']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Favicon URL') }}</label>
                        <input name="site_favicon" value="{{ old('site_favicon', $settings['site_favicon']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div x-data="{ color: '{{ old('primary_color', $settings['primary_color']) }}' }">
                        <label class="block text-sm font-medium text-slate-700">{{ __('Primary color') }}</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="color" x-model="color" class="h-10 w-16 rounded-lg border-slate-300 p-1">
                            <input name="primary_color" x-model="color" value="{{ old('primary_color', $settings['primary_color']) }}" class="flex-1 rounded-lg border-slate-300 text-sm">
                        </div>
                    </div>
                    <div x-data="{ color: '{{ old('accent_color', $settings['accent_color']) }}' }">
                        <label class="block text-sm font-medium text-slate-700">{{ __('Accent color') }}</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="color" x-model="color" class="h-10 w-16 rounded-lg border-slate-300 p-1">
                            <input name="accent_color" x-model="color" value="{{ old('accent_color', $settings['accent_color']) }}" class="flex-1 rounded-lg border-slate-300 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Footer') }}</h2>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700">{{ __('Footer text') }}</label>
                    <input name="footer_text" value="{{ old('footer_text', $settings['footer_text']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                </div>

                <h3 class="mt-6 text-sm font-semibold text-slate-900">{{ __('Social links') }}</h3>
                <template x-for="(item, index) in social" :key="index">
                    <div class="mt-3 flex gap-2">
                        <input type="text" :name="`social_links[${index}][label]`" x-model="item.label" placeholder="Label" class="block w-1/3 rounded-lg border-slate-300 text-sm">
                        <input type="text" :name="`social_links[${index}][url]`" x-model="item.url" placeholder="URL" class="block flex-1 rounded-lg border-slate-300 text-sm">
                        <button type="button" @click="social.splice(index, 1)" class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200">{{ __('Remove') }}</button>
                    </div>
                </template>
                <button type="button" @click="social.push({label: '', url: ''})" class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700 hover:bg-slate-200">{{ __('Add social link') }}</button>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Public navigation') }}</h2>
                <template x-for="(item, index) in menu" :key="index">
                    <div class="mt-3 flex gap-2">
                        <input type="text" :name="`public_nav_menu[${index}][label]`" x-model="item.label" placeholder="Label" class="block w-1/3 rounded-lg border-slate-300 text-sm">
                        <input type="text" :name="`public_nav_menu[${index}][url]`" x-model="item.url" placeholder="URL" class="block flex-1 rounded-lg border-slate-300 text-sm">
                        <button type="button" @click="menu.splice(index, 1)" class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200">{{ __('Remove') }}</button>
                    </div>
                </template>
                <button type="button" @click="menu.push({label: '', url: ''})" class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700 hover:bg-slate-200">{{ __('Add menu item') }}</button>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save changes') }}</button>
        </div>
    </form>
@endsection
