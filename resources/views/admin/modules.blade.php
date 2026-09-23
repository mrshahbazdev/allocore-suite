@extends('layouts.shell')

@section('content')
<div x-data="{
    searchQuery: '',
    selectedCategory: 'all',
    selectedStatus: 'all',
    editingKey: null,
    matches(key, name, desc, cat, inPool, isActive, isDep) {
        const q = this.searchQuery.toLowerCase().trim();
        const matchesQuery = !q || name.toLowerCase().includes(q) || desc.toLowerCase().includes(q) || key.toLowerCase().includes(q);
        const matchesCat = this.selectedCategory === 'all' || cat === this.selectedCategory;
        let matchesStat = true;
        if (this.selectedStatus === 'pool') matchesStat = inPool && !isDep && isActive;
        else if (this.selectedStatus === 'not_pool') matchesStat = !inPool;
        else if (this.selectedStatus === 'deprecated') matchesStat = isDep;
        else if (this.selectedStatus === 'inactive') matchesStat = !isActive;
        return matchesQuery && matchesCat && matchesStat;
    }
}" class="space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 3.096l-.168.169m1.233-1.233l2.87-2.87" />
                    </svg>
                </span>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Werkzeuge & Abo-Tool-Pool') }}</h1>
            </div>
            <p class="mt-1 text-sm text-slate-600">
                {{ __('Verwalten Sie den zentralen Werkzeug-Pool für Kundenabonnements, benennen Sie Tools um, ordnen Sie Kategorien zu und mustern Sie veraltete Module aus.') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('tools.index') }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                {{ __('Kundenansicht öffnen') }}
            </a>
            <a href="{{ route('admin.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">{{ __('Zurück zur Übersicht') }}</a>
        </div>
    </div>

    {{-- Stats Cards & Pool Info Banner --}}
    @php
        $totalTools = $diskModules->count();
        $poolTools = $diskModules->filter(fn($m) => $m['record'] && $m['record']->in_subscription_pool && $m['record']->is_active && !$m['record']->is_deprecated)->count();
        $activeTools = $diskModules->filter(fn($m) => $m['record'] && $m['record']->is_active)->count();
        $deprecatedTools = $diskModules->filter(fn($m) => $m['record'] && $m['record']->is_deprecated)->count();
    @endphp

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Gesamt-Werkzeuge') }}</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-900">{{ $totalTools }}</span>
                <span class="text-xs text-slate-500">{{ __('registriert') }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800">{{ __('Im Abo-Tool-Pool') }}</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-700">{{ $poolTools }}</span>
                <span class="text-xs font-medium text-emerald-600">{{ __('für Kunden freigeschaltet') }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-indigo-200/80 bg-indigo-50/40 p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-indigo-800">{{ __('Aktiv in Suite') }}</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-indigo-700">{{ $activeTools }}</span>
                <span class="text-xs text-indigo-600">{{ __('betriebsbereit') }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/40 p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-800">{{ __('Archiviert / Deprecated') }}</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-amber-700">{{ $deprecatedTools }}</span>
                <span class="text-xs text-amber-600">{{ __('ausgemustert') }}</span>
            </div>
        </div>
    </div>

    {{-- Explanatory Banner --}}
    <div class="flex items-start gap-4 rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50/80 via-blue-50/40 to-slate-50 p-5">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
        </div>
        <div class="text-sm">
            <h3 class="font-bold text-slate-900">{{ __('Wie funktioniert das Abo-Tool-Pool Prinzip?') }}</h3>
            <p class="mt-1 text-slate-600 leading-relaxed">
                Jeder Kunde zahlt einen festen monatlichen Abonnement-Preis und erhält automatisch Zugriff auf alle Werkzeuge im <strong>Abo-Tool-Pool</strong> (auch auf neu hinzugefügte Tools). Sie als Administrator können Werkzeuge per Klick dem Kundenpool zuweisen, Bezeichnungen anpassen oder veraltete Tools ausmustern (<span class="italic">is_deprecated</span>).
            </p>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" x-model="searchQuery" placeholder="{{ __('Tool, Modul-Key oder Beschreibung suchen...') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select x-model="selectedCategory" class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:bg-white focus:outline-none">
                <option value="all">{{ __('Alle Kategorien') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

            <select x-model="selectedStatus" class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:bg-white focus:outline-none">
                <option value="all">{{ __('Alle Status') }}</option>
                <option value="pool">{{ __('✅ Im Abo-Pool') }}</option>
                <option value="not_pool">{{ __('⚪ Nicht im Abo-Pool') }}</option>
                <option value="deprecated">{{ __('⚠️ Archiviert / Deprecated') }}</option>
                <option value="inactive">{{ __('❌ Inaktiv') }}</option>
            </select>
        </div>
    </div>

    {{-- Tool Catalog Grid --}}
    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($diskModules as $module)
            @php($record = $module['record'])
            @php($rawName = $record ? $record->getRawOriginal('name') : $module['name'])
            @php($rawDesc = $record ? $record->getRawOriginal('description') : $module['description'])
            @php($category = $record?->category ?: 'Produktivität & Prozesse')
            @php($icon = $record?->icon ?: 'sparkles')
            @php($badgeText = $record?->badge_text)
            @php($sortOrder = $record?->sort_order ?? 0)
            @php($inPool = $record ? (bool)$record->in_subscription_pool : false)
            @php($isActive = $record ? (bool)$record->is_active : false)
            @php($isDeprecated = $record ? (bool)$record->is_deprecated : false)
            @php($moduleRoles = $record?->allowed_roles ?? [])

            <div x-show="matches('{{ $module['key'] }}', '{{ addslashes($rawName) }}', '{{ addslashes($rawDesc) }}', '{{ $category }}', {{ $inPool ? 'true' : 'false' }}, {{ $isActive ? 'true' : 'false' }}, {{ $isDeprecated ? 'true' : 'false' }})"
                 x-transition
                 class="flex flex-col justify-between rounded-2xl border {{ $isDeprecated ? 'border-amber-200 bg-amber-50/20' : ($inPool ? 'border-slate-200 bg-white' : 'border-slate-200 bg-slate-50/40') }} p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                
                <div>
                    {{-- Card Header: Category, Badges, Status --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                {{ $category }}
                            </span>
                            @if ($badgeText)
                                <span class="inline-flex items-center rounded-lg bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">
                                    {{ $badgeText }}
                                </span>
                            @endif
                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-mono text-slate-600">
                                {{ $module['key'] }}
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if ($isDeprecated)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    {{ __('Archiviert') }}
                                </span>
                            @elseif ($inPool && $isActive)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ __('Abo-Pool') }}
                                </span>
                            @elseif ($isActive)
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                    {{ __('Nur Einzelkauf') }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">
                                    {{ __('Inaktiv') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Tool Title & Meta (View Mode) --}}
                    <div x-show="editingKey !== '{{ $module['key'] }}'" class="mt-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-slate-100 border border-indigo-100/60 text-indigo-600">
                                @include('tools.partials.icon', ['icon' => $icon, 'class' => 'h-6 w-6'])
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-base font-bold text-slate-900 truncate">{{ $record?->name ?: $module['name'] }}</h2>
                                <span class="text-xs text-slate-400 font-mono">/app/{{ $record?->route_prefix ?? $module['alias'] }}</span>
                            </div>
                        </div>

                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                            {{ $record?->description ?: ($module['description'] ?: __('Keine Beschreibung vorhanden. Klicken Sie auf Bearbeiten, um eine Beschreibung hinzuzufügen.')) }}
                        </p>

                        @if ($record && !empty($moduleRoles))
                            <div class="mt-3.5 flex flex-wrap items-center gap-1.5">
                                <span class="text-xs font-medium text-slate-400">{{ __('Rollen-Zugriff:') }}</span>
                                @foreach ($moduleRoles as $role)
                                    <span class="rounded bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700">{{ $role }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Edit Form --}}
                    @if ($record)
                        <form x-show="editingKey === '{{ $module['key'] }}'" x-cloak method="POST" action="{{ route('admin.modules.update', $record) }}" class="mt-4 space-y-4 border-t border-slate-100 pt-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Tool-Anzeigename') }}</label>
                                    <input type="text" name="name" value="{{ old('name', $rawName) }}" class="mt-1 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Kategorie') }}</label>
                                    <select name="category" class="mt-1 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}" {{ old('category', $category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Beschreibung & Nutzen für Kunden') }}</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $rawDesc) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Icon-Key') }}</label>
                                    <input type="text" name="icon" value="{{ old('icon', $icon) }}" class="mt-1 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="z.B. chart-bar">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Badge-Text (optional)') }}</label>
                                    <input type="text" name="badge_text" value="{{ old('badge_text', $badgeText) }}" class="mt-1 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="z.B. Kern-Tool, Beliebt">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Sortierung (0-99)') }}</label>
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $sortOrder) }}" class="mt-1 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('URL-Pfad') }}</label>
                                    <div class="mt-1 flex rounded-xl shadow-sm">
                                        <span class="inline-flex items-center rounded-l-xl border border-r-0 border-slate-300 bg-slate-50 px-3 text-xs text-slate-500">/app/</span>
                                        <input type="text" name="route_prefix" value="{{ old('route_prefix', $record->route_prefix) }}" class="block w-full rounded-r-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div class="space-y-2 pt-2">
                                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                        <input type="checkbox" name="in_subscription_pool" value="1" {{ old('in_subscription_pool', $record->in_subscription_pool) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-emerald-900 font-semibold">{{ __('Im Kunden-Abo-Tool-Pool freigeschaltet') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $record->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ __('Modul betriebsbereit / aktiv') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                        <input type="checkbox" name="is_deprecated" value="1" {{ old('is_deprecated', $record->is_deprecated) ? 'checked' : '' }} class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                        <span class="text-amber-800">{{ __('Veraltet / Archiviert (im Kundenbereich ausblenden)') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Rollen-Berechtigungen (optional)') }}</label>
                                <p class="text-xs text-slate-400 mb-2">{{ __('Leer lassen, um allen Abonnenten Zugriff zu gewähren.') }}</p>
                                <div class="flex flex-wrap gap-3">
                                    @foreach ($roles as $role)
                                        <label class="flex items-center gap-1.5 text-xs text-slate-700">
                                            <input type="checkbox" name="allowed_roles[]" value="{{ $role->name }}" {{ in_array($role->name, $moduleRoles, true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span>{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="editingKey = null" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    {{ __('Abbrechen') }}
                                </button>
                                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    {{ __('Änderungen speichern') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Action Bar --}}
                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    @if (! $record)
                        <form method="POST" action="{{ route('admin.modules.install', $module['name']) }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                                {{ __('Installieren & Aktivieren') }}
                            </button>
                        </form>
                    @else
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Edit Button --}}
                            <button type="button" @click="editingKey = editingKey === '{{ $module['key'] }}' ? null : '{{ $module['key'] }}'" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                                <span x-text="editingKey === '{{ $module['key'] }}' ? '{{ __('Schließen') }}' : '{{ __('Bearbeiten') }}'"></span>
                            </button>

                            {{-- Tool Pool Toggle --}}
                            <form method="POST" action="{{ route('admin.modules.toggle-pool', $record) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="{{ $record->in_subscription_pool ? __('Aus dem Kunden-Abo-Pool entfernen') : __('Dem Kunden-Abo-Pool hinzufügen') }}" class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $record->in_subscription_pool ? 'border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : 'border border-slate-300 bg-white text-slate-600 hover:bg-slate-50' }}">
                                    <span>{{ $record->in_subscription_pool ? 'Pool: Aktiv' : 'Pool: Inaktiv' }}</span>
                                </button>
                            </form>

                            {{-- Active Toggle --}}
                            <form method="POST" action="{{ route('admin.modules.toggle', $record) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-xl px-2.5 py-1.5 text-xs font-semibold transition {{ $record->is_active ? 'border border-slate-200 bg-slate-100 text-slate-700 hover:bg-slate-200' : 'border border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                    {{ $record->is_active ? __('Deaktivieren') : __('Aktivieren') }}
                                </button>
                            </form>

                            {{-- Deprecate Toggle --}}
                            <form method="POST" action="{{ route('admin.modules.toggle-deprecate', $record) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="{{ $record->is_deprecated ? __('Wiederherstellen') : __('Als veraltet markieren') }}" class="rounded-xl px-2.5 py-1.5 text-xs font-semibold transition {{ $record->is_deprecated ? 'border border-amber-300 bg-amber-100 text-amber-900 hover:bg-amber-200' : 'border border-slate-200 bg-white text-slate-400 hover:text-amber-700 hover:border-amber-200' }}">
                                    {{ $record->is_deprecated ? __('Wiederherstellen') : __('Archivieren') }}
                                </button>
                            </form>
                        </div>

                        @if ($record->is_active && !$record->is_deprecated)
                            <a href="{{ url('app/'.$record->route_prefix) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                <span>{{ __('Tool öffnen') }}</span>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
