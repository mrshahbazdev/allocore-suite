@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Modules') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Install, activate and manage tools like WordPress plugins.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($diskModules as $module)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">{{ $module['alias'] }}</div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ $module['name'] }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $module['description'] ?: __('No description available.') }}</p>
                    </div>
                    @if ($module['record'])
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $module['record']->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $module['record']->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">{{ __('Not installed') }}</span>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if (! $module['record'])
                        <form method="POST" action="{{ route('admin.modules.install', $module['name']) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Install & Activate') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.modules.toggle', $module['record']) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold {{ $module['record']->is_active ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-green-600 text-white hover:bg-green-500' }}">
                                {{ $module['record']->is_active ? __('Deactivate') : __('Activate') }}
                            </button>
                        </form>

                        @php($moduleRoles = $module['record']->allowed_roles ?? [])
                        <form method="POST" action="{{ route('admin.modules.update', $module['record']) }}" class="mt-3 w-full">
                            @csrf
                            @method('PUT')
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Allowed roles') }}</div>
                            <div class="mt-2 flex flex-wrap gap-3">
                                @foreach ($roles as $role)
                                    <label class="flex items-center gap-1.5">
                                        <input type="checkbox" name="allowed_roles[]" value="{{ $role->name }}" {{ in_array($role->name, $moduleRoles, true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-slate-700">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit" class="mt-3 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Update roles') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
