@extends('layouts.shell')

@section('content')
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Tools & Modules') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('Rename tools, customize descriptions, configure permissions, and manage activations.') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tools.index') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                {{ __('View Tools Page') }}
            </a>
            <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to Admin') }}</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($diskModules as $module)
            @php($record = $module['record'])
            @php($rawName = $record ? $record->getRawOriginal('name') : $module['name'])
            @php($rawDesc = $record ? $record->getRawOriginal('description') : $module['description'])
            @php($moduleRoles = $record?->allowed_roles ?? [])

            <div x-data="{ editing: false }" class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300">
                <div>
                    {{-- Header: Badges & Status --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-mono font-medium text-slate-700">
                                {{ $module['key'] }}
                            </span>
                            <span class="text-xs text-slate-400">/app/{{ $record?->route_prefix ?? $module['alias'] }}</span>
                        </div>
                        @if ($record)
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $record->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $record->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $record->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">{{ __('Not installed') }}</span>
                        @endif
                    </div>

                    {{-- Tool Title & Description View Mode --}}
                    <div x-show="!editing" class="mt-4">
                        <h2 class="text-lg font-bold text-slate-900">{{ $record?->name ?: $module['name'] }}</h2>
                        <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">
                            {{ $record?->description ?: ($module['description'] ?: __('No description added yet. Click edit to explain what this tool does.')) }}
                        </p>

                        @if ($record && !empty($moduleRoles))
                            <div class="mt-4 flex flex-wrap items-center gap-1.5">
                                <span class="text-xs font-medium text-slate-400">{{ __('Access:') }}</span>
                                @foreach ($moduleRoles as $role)
                                    <span class="rounded bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700">{{ $role }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Tool Edit Form (Name, Description, Prefix, Roles) --}}
                    @if ($record)
                        <form x-show="editing" x-cloak method="POST" action="{{ route('admin.modules.update', $record) }}" class="mt-4 space-y-4 border-t border-slate-100 pt-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Tool Display Name') }}</label>
                                <input type="text" name="name" value="{{ old('name', $rawName) }}" class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('e.g. CashFlow & Liquiditätsmanager') }}" required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Tool Purpose & Description') }}</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Explain clearly what problem this tool solves and why the user needs it...') }}">{{ old('description', $rawDesc) }}</textarea>
                                <p class="mt-1 text-xs text-slate-400">{{ __('This text is shown on the dashboard and tools directory so users understand what they are dealing with.') }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Route URL Prefix') }}</label>
                                    <div class="mt-1 flex rounded-lg shadow-sm">
                                        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-2.5 text-xs text-slate-500">/app/</span>
                                        <input type="text" name="route_prefix" value="{{ old('route_prefix', $record->route_prefix) }}" class="block w-full rounded-r-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div class="flex items-center pt-5">
                                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $record->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ __('Active in Suite') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">{{ __('Allowed Access Roles') }}</label>
                                <p class="text-xs text-slate-400 mb-2">{{ __('Leave empty to allow all registered users with active subscription.') }}</p>
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
                                <button type="button" @click="editing = false" class="rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    {{ __('Save Changes') }}
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
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                                {{ __('Install & Activate') }}
                            </button>
                        </form>
                    @else
                        <div class="flex items-center gap-2">
                            <button type="button" @click="editing = !editing" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                                <span x-text="editing ? '{{ __('Close Editor') }}' : '{{ __('Edit Name & Description') }}'"></span>
                            </button>

                            <form method="POST" action="{{ route('admin.modules.toggle', $record) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $record->is_active ? 'border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                    {{ $record->is_active ? __('Deactivate') : __('Activate') }}
                                </button>
                            </form>
                        </div>

                        @if ($record->is_active)
                            <a href="{{ url('app/'.$record->route_prefix) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                <span>{{ __('Open Tool') }}</span>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
