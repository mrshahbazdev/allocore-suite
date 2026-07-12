@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Manage Plans') }}</h1>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 space-y-4">
            @foreach ($plans as $plan)
                <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-slate-900">{{ $plan->name }}</h2>
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($plan->is_active) class="rounded border-slate-300">
                            {{ __('Active') }}
                        </label>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input type="text" name="name" value="{{ $plan->name }}" class="rounded-lg border-slate-300 text-sm" placeholder="{{ __('Name') }}">
                        <select name="billable_scope" class="rounded-lg border-slate-300 text-sm">
                            @foreach (['user' => __('User only'), 'team' => __('Team only'), 'both' => __('User & Team')] as $value => $label)
                                <option value="{{ $value }}" @selected($plan->billable_scope === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" name="price_monthly" value="{{ $plan->price_monthly }}" class="rounded-lg border-slate-300 text-sm" placeholder="{{ __('Monthly price') }}">
                        <input type="number" step="0.01" name="price_yearly" value="{{ $plan->price_yearly }}" class="rounded-lg border-slate-300 text-sm" placeholder="{{ __('Yearly price') }}">
                        <input type="text" name="currency" value="{{ $plan->currency }}" maxlength="3" class="rounded-lg border-slate-300 text-sm" placeholder="EUR">
                    </div>
                    <textarea name="description" rows="2" class="mt-3 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('Description') }}">{{ $plan->description }}</textarea>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @foreach ($modules as $module)
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="modules[]" value="{{ $module->id }}" @checked($plan->modules->contains($module)) class="rounded border-slate-300">
                                {{ $module->name }}
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
                        <button form="delete-plan-{{ $plan->id }}" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                    </div>
                </form>
                <form id="delete-plan-{{ $plan->id }}" method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('{{ __('Delete this plan?') }}')">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.plans.store') }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm h-fit space-y-3">
            @csrf
            <h2 class="font-semibold text-slate-900">{{ __('New plan') }}</h2>
            <input type="text" name="name" required class="w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('Name') }}">
            <textarea name="description" rows="2" class="w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('Description') }}"></textarea>
            <div class="grid grid-cols-2 gap-2">
                <input type="number" step="0.01" name="price_monthly" required class="rounded-lg border-slate-300 text-sm" placeholder="{{ __('Monthly') }}">
                <input type="number" step="0.01" name="price_yearly" required class="rounded-lg border-slate-300 text-sm" placeholder="{{ __('Yearly') }}">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input type="text" name="currency" value="EUR" maxlength="3" class="rounded-lg border-slate-300 text-sm">
                <select name="billable_scope" class="rounded-lg border-slate-300 text-sm">
                    <option value="both">{{ __('User & Team') }}</option>
                    <option value="user">{{ __('User only') }}</option>
                    <option value="team">{{ __('Team only') }}</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach ($modules as $module)
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="modules[]" value="{{ $module->id }}" class="rounded border-slate-300">
                        {{ $module->name }}
                    </label>
                @endforeach
            </div>
            <input type="hidden" name="is_active" value="1">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create plan') }}</button>
        </form>
    </div>
@endsection
