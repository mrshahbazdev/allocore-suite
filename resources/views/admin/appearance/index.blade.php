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
        siteName: '{{ old('site_name', $settings['site_name']) }}',
        siteLogo: '{{ old('site_logo', $settings['site_logo']) }}',
        primaryColor: '{{ old('primary_color', $settings['primary_color']) }}',
        menuGap: {{ (int) old('menu_gap', $settings['menu_gap'] ?? 28) }},
        menuPaddingX: {{ (int) old('menu_padding_x', $settings['menu_padding_x'] ?? 14) }},
        menuPaddingY: {{ (int) old('menu_padding_y', $settings['menu_padding_y'] ?? 8) }},
        menuFontSize: {{ (int) old('menu_font_size', $settings['menu_font_size'] ?? 15) }},
        menuFontWeight: '{{ old('menu_font_weight', $settings['menu_font_weight'] ?? '600') }}',
        menuLinkColor: '{{ old('menu_link_color', $settings['menu_link_color'] ?? '#334155') }}',
        menuHoverColor: '{{ old('menu_hover_color', $settings['menu_hover_color'] ?? '#4f46e5') }}',
        previewHoveredIndex: null,
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
        },
        resetMenuStyles() {
            this.menuGap = 28;
            this.menuPaddingX = 14;
            this.menuPaddingY = 8;
            this.menuFontSize = 15;
            this.menuFontWeight = '600';
            this.menuLinkColor = '#334155';
            this.menuHoverColor = '#4f46e5';
        }
    }">
        @csrf
        @method('PUT')

        {{-- Hidden Inputs for Menu Styling --}}
        <input type="hidden" name="menu_gap" :value="menuGap">
        <input type="hidden" name="menu_padding_x" :value="menuPaddingX">
        <input type="hidden" name="menu_padding_y" :value="menuPaddingY">
        <input type="hidden" name="menu_font_size" :value="menuFontSize">
        <input type="hidden" name="menu_font_weight" :value="menuFontWeight">
        <input type="hidden" name="menu_link_color" :value="menuLinkColor">
        <input type="hidden" name="menu_hover_color" :value="menuHoverColor">

        <div class="space-y-6">
            {{-- LIVE INTERACTIVE HEADER PREVIEW --}}
            <div class="sticky top-4 z-40 overflow-hidden rounded-2xl border-2 border-indigo-500/50 bg-slate-900/95 p-5 shadow-2xl backdrop-blur-md">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                        </span>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">{{ __('Real-Time Menu Live Preview') }}</h2>
                        <span class="rounded bg-indigo-500/20 px-2 py-0.5 text-xs text-indigo-300">{{ __('Changes reflect instantly') }}</span>
                    </div>
                    <div class="text-xs text-slate-400">
                        <span>{{ __('Gap:') }} <strong class="text-white font-mono" x-text="menuGap + 'px'"></strong></span>
                        <span class="mx-1.5">·</span>
                        <span>{{ __('Padding:') }} <strong class="text-white font-mono" x-text="`${menuPaddingY}px ${menuPaddingX}px`"></strong></span>
                        <span class="mx-1.5">·</span>
                        <span>{{ __('Size:') }} <strong class="text-white font-mono" x-text="menuFontSize + 'px'"></strong></span>
                    </div>
                </div>

                {{-- Mockup Browser Header --}}
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mx-auto flex min-w-[650px] items-center justify-between">
                        {{-- Brand Logo / Name --}}
                        <div class="flex items-center gap-2.5 shrink-0">
                            <img :src="siteLogo || '{{ asset('logo-mark.png') }}'" alt="" class="h-9 w-9 object-contain rounded-lg bg-slate-50 border border-slate-100 p-0.5">
                            <span class="text-base font-bold text-slate-900" x-text="siteName || '{{ config('app.name') }}'"></span>
                        </div>

                        {{-- Dynamic Navigation Preview Items --}}
                        <nav class="flex items-center mx-4 transition-all duration-150" :style="`gap: ${menuGap}px;`">
                            <template x-if="menu.length === 0">
                                <span class="text-xs text-slate-400 italic">{{ __('Default links will show (Glossary, Blog, Pricing, API Docs)') }}</span>
                            </template>

                            <template x-for="(item, idx) in menu" :key="idx">
                                <div>
                                    <template x-if="!item.children || item.children.length === 0">
                                        <a href="javascript:void(0)"
                                           class="inline-flex items-center rounded-lg transition-all duration-150"
                                           :style="`padding: ${menuPaddingY}px ${menuPaddingX}px; font-size: ${menuFontSize}px; font-weight: ${menuFontWeight}; color: ${previewHoveredIndex === idx ? menuHoverColor : menuLinkColor}; background-color: ${previewHoveredIndex === idx ? '#f1f5f9' : 'transparent'}; text-decoration: none; white-space: nowrap;`"
                                           @mouseenter="previewHoveredIndex = idx"
                                           @mouseleave="previewHoveredIndex = null"
                                           x-text="item.label || '{{ __('Item') }}'">
                                        </a>
                                    </template>

                                    <template x-if="item.children && item.children.length > 0">
                                        <div class="relative" x-data="{ open: false }" @mouseenter="open = true; previewHoveredIndex = idx" @mouseleave="open = false; previewHoveredIndex = null">
                                            <button type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-lg transition-all duration-150"
                                                    :style="`padding: ${menuPaddingY}px ${menuPaddingX}px; font-size: ${menuFontSize}px; font-weight: ${menuFontWeight}; color: ${previewHoveredIndex === idx ? menuHoverColor : menuLinkColor}; background-color: ${previewHoveredIndex === idx ? '#f1f5f9' : 'transparent'}; white-space: nowrap;`">
                                                <span x-text="item.label || '{{ __('Dropdown') }}'"></span>
                                                <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-cloak class="absolute left-0 top-full z-50 mt-1 w-52 rounded-xl border border-slate-200 bg-white p-2 shadow-xl ring-1 ring-black/5">
                                                <template x-for="(child, cIdx) in item.children" :key="cIdx">
                                                    <div class="block rounded-lg px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition" x-text="child.label || 'Sub-item'"></div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </nav>

                        {{-- Right Buttons Mockup --}}
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-xs font-semibold text-slate-600">{{ __('Login') }}</span>
                            <span class="rounded-lg px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs" :style="`background-color: ${primaryColor || '#ff9200'};`">{{ __('Get Started') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MENU SPACING & STYLING CUSTOMIZER CONTROLS --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Menu Spacing & Style Customizer') }}</h2>
                        <p class="text-sm text-slate-500">{{ __('Fine-tune margin, padding, font size, and colors with instant real-time live preview.') }}</p>
                    </div>
                    <button type="button" @click="resetMenuStyles()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        {{ __('Reset to Defaults') }}
                    </button>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- Gap / Spacing between items --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('Menu Item Gap / Margin') }}</label>
                            <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-mono font-bold text-indigo-700" x-text="menuGap + ' px'"></span>
                        </div>
                        <input type="range" min="0" max="64" step="2" x-model.number="menuGap" class="w-full accent-indigo-600">
                        <div class="mt-2 flex justify-between text-[11px] text-slate-400">
                            <span>0px (Tight)</span>
                            <span>28px (Default)</span>
                            <span>64px (Spacious)</span>
                        </div>
                    </div>

                    {{-- Horizontal Padding --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('Item Horizontal Padding (X)') }}</label>
                            <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-mono font-bold text-indigo-700" x-text="menuPaddingX + ' px'"></span>
                        </div>
                        <input type="range" min="0" max="36" step="2" x-model.number="menuPaddingX" class="w-full accent-indigo-600">
                        <div class="mt-2 flex justify-between text-[11px] text-slate-400">
                            <span>0px</span>
                            <span>14px (Default)</span>
                            <span>36px</span>
                        </div>
                    </div>

                    {{-- Vertical Padding --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('Item Vertical Padding (Y)') }}</label>
                            <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-mono font-bold text-indigo-700" x-text="menuPaddingY + ' px'"></span>
                        </div>
                        <input type="range" min="0" max="24" step="1" x-model.number="menuPaddingY" class="w-full accent-indigo-600">
                        <div class="mt-2 flex justify-between text-[11px] text-slate-400">
                            <span>0px</span>
                            <span>8px (Default)</span>
                            <span>24px</span>
                        </div>
                    </div>

                    {{-- Font Size --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('Font Size') }}</label>
                            <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-mono font-bold text-indigo-700" x-text="menuFontSize + ' px'"></span>
                        </div>
                        <input type="range" min="12" max="24" step="1" x-model.number="menuFontSize" class="w-full accent-indigo-600">
                        <div class="mt-2 flex justify-between text-[11px] text-slate-400">
                            <span>12px (Small)</span>
                            <span>15px (Default)</span>
                            <span>24px (Large)</span>
                        </div>
                    </div>

                    {{-- Font Weight --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">{{ __('Font Weight') }}</label>
                        <select x-model="menuFontWeight" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="400">{{ __('400 - Normal') }}</option>
                            <option value="500">{{ __('500 - Medium') }}</option>
                            <option value="600">{{ __('600 - Semibold (Default)') }}</option>
                            <option value="700">{{ __('700 - Bold') }}</option>
                            <option value="800">{{ __('800 - Extra Bold') }}</option>
                        </select>
                    </div>

                    {{-- Colors --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">{{ __('Link & Hover Color') }}</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[11px] text-slate-500 block mb-1">{{ __('Link Color') }}</span>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="menuLinkColor" class="h-8 w-10 cursor-pointer rounded border-slate-300 p-0.5">
                                    <input type="text" x-model="menuLinkColor" class="w-full rounded-md border-slate-300 text-xs font-mono">
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 block mb-1">{{ __('Hover Color') }}</span>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" x-model="menuHoverColor" class="h-8 w-10 cursor-pointer rounded border-slate-300 p-0.5">
                                    <input type="text" x-model="menuHoverColor" class="w-full rounded-md border-slate-300 text-xs font-mono">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Branding Card --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Branding') }}</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Site name') }}</label>
                        <input name="site_name" x-model="siteName" value="{{ old('site_name', $settings['site_name']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
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
                        <input name="site_logo" x-model="siteLogo" value="{{ old('site_logo', $settings['site_logo']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Favicon URL') }}</label>
                        <input name="site_favicon" value="{{ old('site_favicon', $settings['site_favicon']) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Primary color') }}</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="color" x-model="primaryColor" class="h-10 w-16 rounded-lg border-slate-300 p-1">
                            <input name="primary_color" x-model="primaryColor" value="{{ old('primary_color', $settings['primary_color']) }}" class="flex-1 rounded-lg border-slate-300 text-sm">
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

            {{-- Footer Card --}}
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
