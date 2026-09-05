@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Appearance') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Customize your site branding, colors, menus and footer like a WordPress theme.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.appearance.update') }}" x-data="{
        menu: ({{ json_encode(old('public_nav_menu', $settings['public_nav_menu']) ?: []) }}).map(i => ({
            label: i.label || '',
            url: i.url || '',
            children: Array.isArray(i.children) ? i.children.map(c => ({ label: c.label || '', url: c.url || '' })) : []
        })),
        social: {{ json_encode(old('social_links', $settings['social_links']) ?: []) }},
        addMenuItem(label = '', url = '') {
            this.menu.push({ label: label, url: url, children: [] });
        },
        removeMenuItem(index) {
            this.menu.splice(index, 1);
        },
        moveMenuItem(index, dir) {
            const target = index + dir;
            if (target < 0 || target >= this.menu.length) return;
            const item = this.menu.splice(index, 1)[0];
            this.menu.splice(target, 0, item);
        },
        addSubItem(pIndex, label = '', url = '') {
            if (!this.menu[pIndex].children) this.menu[pIndex].children = [];
            this.menu[pIndex].children.push({ label: label, url: url });
        },
        removeSubItem(pIndex, cIndex) {
            this.menu[pIndex].children.splice(cIndex, 1);
        }
    }">
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
                            <option value="figtree" {{ old('font_family', $settings['font_family']) === 'figtree' ? 'selected' : '' }}>{{ __('Figtree') }}</option>
                            <option value="inter" {{ old('font_family', $settings['font_family']) === 'inter' ? 'selected' : '' }}>{{ __('Inter') }}</option>
                            <option value="roboto" {{ old('font_family', $settings['font_family']) === 'roboto' ? 'selected' : '' }}>{{ __('Roboto') }}</option>
                            <option value="poppins" {{ old('font_family', $settings['font_family']) === 'poppins' ? 'selected' : '' }}>{{ __('Poppins') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Dashboard template') }}</label>
                        <select name="dashboard_template" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                            <option value="default" {{ old('dashboard_template', $settings['dashboard_template']) === 'default' ? 'selected' : '' }}>{{ __('Default') }}</option>
                            <option value="executive" {{ old('dashboard_template', $settings['dashboard_template']) === 'executive' ? 'selected' : '' }}>{{ __('Executive') }}</option>
                            <option value="operations" {{ old('dashboard_template', $settings['dashboard_template']) === 'operations' ? 'selected' : '' }}>{{ __('Operations') }}</option>
                            <option value="minimal" {{ old('dashboard_template', $settings['dashboard_template']) === 'minimal' ? 'selected' : '' }}>{{ __('Minimal') }}</option>
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
                        <input type="text" :name="`social_links[${index}][label]`" x-model="item.label" placeholder="{{ __('Label') }}" class="block w-1/3 rounded-lg border-slate-300 text-sm">
                        <input type="text" :name="`social_links[${index}][url]`" x-model="item.url" placeholder="{{ __('URL') }}" class="block flex-1 rounded-lg border-slate-300 text-sm">
                        <button type="button" @click="social.splice(index, 1)" class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200">{{ __('Remove') }}</button>
                    </div>
                </template>
                <button type="button" @click="social.push({label: '', url: ''})" class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700 hover:bg-slate-200">{{ __('Add social link') }}</button>
            </div>

            {{-- Navigation Menu & Submenus Builder --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Site Navigation & Submenus') }}</h2>
                        <p class="text-sm text-slate-500">{{ __('Build your header navigation with top-level links and dropdown submenus. Visible both logged in and logged out.') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="addMenuItem('', '')" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Add Menu Item') }}
                        </button>
                    </div>
                </div>

                {{-- Quick Insert Suggestions --}}
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                    <span class="font-medium text-slate-500">{{ __('Quick Add:') }}</span>
                    <button type="button" @click="addMenuItem('Home', '/')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-slate-700 hover:bg-slate-100 transition">+ Home</button>
                    <button type="button" @click="addMenuItem('Glossary', '{{ route('glossary.index') }}')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-slate-700 hover:bg-slate-100 transition">+ Glossary</button>
                    <button type="button" @click="addMenuItem('Blog', '{{ route('blog.index') }}')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-slate-700 hover:bg-slate-100 transition">+ Blog</button>
                    <button type="button" @click="addMenuItem('Pricing', '{{ route('billing.plans') }}')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-slate-700 hover:bg-slate-100 transition">+ Pricing</button>
                    <button type="button" @click="addMenuItem('Audit Example', '{{ route('audit-example.index') }}')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-slate-700 hover:bg-slate-100 transition">+ Audit Example</button>
                    <button type="button" @click="addMenuItem('Case Studies', '{{ route('case-studies.index') }}')" class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-slate-700 hover:bg-slate-100 transition">+ Case Studies</button>
                </div>

                {{-- Menu Items List --}}
                <div class="mt-6 space-y-4">
                    <template x-for="(item, index) in menu" :key="index">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 shadow-sm transition hover:border-slate-300">
                            {{-- Top-level Item Header --}}
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div class="flex items-center gap-1.5 text-slate-400">
                                    <button type="button" @click="moveMenuItem(index, -1)" :disabled="index === 0" class="rounded p-1 hover:bg-slate-200 disabled:opacity-30" title="{{ __('Move Up') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" @click="moveMenuItem(index, 1)" :disabled="index === menu.length - 1" class="rounded p-1 hover:bg-slate-200 disabled:opacity-30" title="{{ __('Move Down') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <span class="ml-1 flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700" x-text="index + 1"></span>
                                </div>

                                <div class="grid flex-1 gap-2 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Menu Label') }}</label>
                                        <input type="text" :name="`public_nav_menu[${index}][label]`" x-model="item.label" placeholder="{{ __('e.g. Products or Company') }}" class="block w-full rounded-lg border-slate-300 text-sm font-medium focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            {{ __('Link URL') }}
                                            <span class="text-slate-400 font-normal">({{ __('use # if dropdown parent') }})</span>
                                        </label>
                                        <input type="text" :name="`public_nav_menu[${index}][url]`" x-model="item.url" placeholder="{{ __('e.g. /products or #') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 sm:self-end">
                                    <button type="button" @click="addSubItem(index, '', '')" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        {{ __('+ Submenu') }}
                                    </button>
                                    <button type="button" @click="removeMenuItem(index)" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Submenus Section --}}
                            <div class="mt-4 pl-6 sm:pl-10 border-l-2 border-indigo-200 space-y-3" x-show="item.children && item.children.length > 0">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        {{ __('Dropdown Submenu Items') }} (<span x-text="item.children ? item.children.length : 0"></span>)
                                    </span>
                                    <button type="button" @click="addSubItem(index, '', '')" class="text-xs font-semibold text-indigo-600 hover:underline">
                                        {{ __('+ Add another sub-item') }}
                                    </button>
                                </div>

                                <template x-for="(child, cIndex) in item.children" :key="cIndex">
                                    <div class="flex items-center gap-2 rounded-lg bg-white p-2.5 border border-slate-200 shadow-xs">
                                        <div class="text-slate-400 text-xs font-mono px-1" x-text="`${index+1}.${cIndex+1}`"></div>
                                        <input type="text" :name="`public_nav_menu[${index}][children][${cIndex}][label]`" x-model="child.label" placeholder="{{ __('Submenu Title') }}" class="block w-1/3 rounded-md border-slate-300 text-xs font-medium focus:border-indigo-500 focus:ring-indigo-500">
                                        <input type="text" :name="`public_nav_menu[${index}][children][${cIndex}][url]`" x-model="child.url" placeholder="{{ __('Submenu URL (e.g. /app/audit-pro)') }}" class="block flex-1 rounded-md border-slate-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                        <button type="button" @click="removeSubItem(index, cIndex)" class="rounded p-1.5 text-rose-500 hover:bg-rose-50 hover:text-rose-700" title="{{ __('Remove submenu item') }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div x-show="menu.length === 0" class="rounded-xl border border-dashed border-slate-300 p-8 text-center">
                        <p class="text-sm text-slate-500">{{ __('No custom menu items yet. Default links (Glossary, Blog, Pricing, API Docs) are used.') }}</p>
                        <button type="button" @click="addMenuItem('', '')" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm">
                            {{ __('Create First Menu Item') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm">{{ __('Save changes') }}</button>
        </div>
    </form>
@endsection
