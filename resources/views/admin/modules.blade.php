@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Modules') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Enable or disable tools across the platform.') }}</p>
        </div>
        <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($modules as $module)
            <form method="POST" action="{{ route('admin.modules.update', $module) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PUT')
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">{{ $module->key }}</div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ $module->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $module->description }}</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($module->is_active) class="rounded border-slate-300">
                        {{ __('Active') }}
                    </label>
                </div>

                <div class="mt-4 text-sm text-slate-600">
                    {{ __('Route prefix') }}: <span class="font-medium text-slate-900">{{ $module->route_prefix }}</span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($module->plans as $plan)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $plan->name }}</span>
                    @endforeach
                </div>

                <button class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </form>
        @endforeach
    </div>
@endsection
