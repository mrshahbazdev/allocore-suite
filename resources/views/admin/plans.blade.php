@extends('layouts.shell')

@section('content')
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Bundles & Pricing') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('Create bundles of tools, set monthly and yearly prices, and control which plans are active.') }}</p>
        </div>
        <a href="{{ route('billing.plans') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            {{ __('View Pricing Page') }}
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            @forelse ($plans as $plan)
                <div class="card" x-data="{ editing: false }">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">{{ $plan->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                @if ($plan->price_monthly == 0 && $plan->price_yearly == 0)
                                    {{ __('Free') }}
                                @else
                                    {{ number_format($plan->price_monthly, 2) }} {{ $plan->currency }} / {{ __('month') }}
                                    &middot;
                                    {{ number_format($plan->price_yearly, 2) }} {{ $plan->currency }} / {{ __('year') }}
                                @endif
                                &middot;
                                <span class="text-xs uppercase tracking-wide {{ $plan->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                                    {{ $plan->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="editing = !editing" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                                <span x-text="editing ? '{{ __('Close') }}' : '{{ __('Edit') }}'"></span>
                            </button>
                        </div>
                    </div>

                    @if ($plan->modules->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-2" x-show="!editing">
                            @foreach ($plan->modules as $module)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    {{ $module->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <form x-show="editing" x-cloak method="POST" action="{{ route('admin.plans.update', $plan) }}" class="mt-6 space-y-5 border-t border-slate-100 pt-5">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="sm:col-span-2">
                                <label class="form-label" for="name-{{ $plan->id }}">{{ __('Bundle name') }}</label>
                                <input id="name-{{ $plan->id }}" type="text" name="name" value="{{ old('name', $plan->name) }}" class="form-control" required>
                            </div>

                            <div>
                                <label class="form-label" for="price_monthly-{{ $plan->id }}">{{ __('Monthly price') }}</label>
                                <input id="price_monthly-{{ $plan->id }}" type="number" step="0.01" min="0" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" class="form-control" required>
                            </div>

                            <div>
                                <label class="form-label" for="price_yearly-{{ $plan->id }}">{{ __('Yearly price') }}</label>
                                <input id="price_yearly-{{ $plan->id }}" type="number" step="0.01" min="0" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" class="form-control" required>
                            </div>

                            <div>
                                <label class="form-label" for="currency-{{ $plan->id }}">{{ __('Currency') }}</label>
                                <input id="currency-{{ $plan->id }}" type="text" name="currency" value="{{ old('currency', $plan->currency) }}" maxlength="3" class="form-control uppercase" required>
                            </div>

                            <div>
                                <label class="form-label" for="billable_scope-{{ $plan->id }}">{{ __('Billable scope') }}</label>
                                <select id="billable_scope-{{ $plan->id }}" name="billable_scope" class="form-control">
                                    @foreach (['user' => __('User only'), 'team' => __('Team only'), 'both' => __('User & Team')] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('billable_scope', $plan->billable_scope) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="form-label" for="description-{{ $plan->id }}">{{ __('Description') }}</label>
                                <textarea id="description-{{ $plan->id }}" name="description" rows="2" class="form-control">{{ old('description', $plan->description) }}</textarea>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="form-label">{{ __('Included tools') }}</label>
                                <div class="mt-2 flex flex-wrap gap-3">
                                    @foreach ($modules as $module)
                                        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                            <input type="checkbox" name="modules[]" value="{{ $module->id }}" @checked(collect(old('modules', $plan->modules->pluck('id')->toArray()))->contains($module->id)) class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                                            {{ $module->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="sm:col-span-2 flex items-center gap-2">
                                <input type="hidden" name="is_active" value="0">
                                <input id="is_active-{{ $plan->id }}" type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active)) class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                                <label for="is_active-{{ $plan->id }}" class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="editing = false" class="btn btn-secondary btn-sm">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Save bundle') }}</button>
                            <button type="submit" form="delete-plan-{{ $plan->id }}" class="btn btn-danger btn-sm ml-auto">{{ __('Delete') }}</button>
                        </div>
                    </form>

                    <form id="delete-plan-{{ $plan->id }}" method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('{{ __('Delete this bundle?') }}')">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @empty
                <div class="card flex flex-col items-center justify-center py-12 text-center">
                    <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.106V8.78M2.25 18.75c.936.135 1.905.211 2.9.211M2.25 18.75c.657.097 1.335.145 2.04.145M18.75 9.286V6.375a2.25 2.25 0 00-1.244-2.013l-2.9-1.449A2.25 2.25 0 0012 2.25a2.25 2.25 0 00-2.1 1.663l-2.9 1.449A2.25 2.25 0 005.625 6.375v2.91M18.75 9.286h-3.375M5.625 9.286h3.375m6.75-6.896v4.5"/></svg>
                    <p class="mt-3 text-sm text-slate-500">{{ __('No bundles yet.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="card h-fit">
            <h2 class="card-title">{{ __('New bundle') }}</h2>
            <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="form-label" for="create-name">{{ __('Bundle name') }}</label>
                    <input id="create-name" type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="{{ __('e.g. All Tools Bundle') }}" required>
                </div>

                <div>
                    <label class="form-label" for="create-description">{{ __('Description') }}</label>
                    <textarea id="create-description" name="description" rows="2" class="form-control" placeholder="{{ __('Describe what is included in this bundle.') }}">{{ old('description') }}</textarea>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-label" for="create-price_monthly">{{ __('Monthly price') }}</label>
                        <input id="create-price_monthly" type="number" step="0.01" min="0" name="price_monthly" value="{{ old('price_monthly', '0.00') }}" class="form-control" required>
                    </div>

                    <div>
                        <label class="form-label" for="create-price_yearly">{{ __('Yearly price') }}</label>
                        <input id="create-price_yearly" type="number" step="0.01" min="0" name="price_yearly" value="{{ old('price_yearly', '0.00') }}" class="form-control" required>
                    </div>

                    <div>
                        <label class="form-label" for="create-currency">{{ __('Currency') }}</label>
                        <input id="create-currency" type="text" name="currency" value="{{ old('currency', 'EUR') }}" maxlength="3" class="form-control uppercase" required>
                    </div>

                    <div>
                        <label class="form-label" for="create-billable_scope">{{ __('Billable scope') }}</label>
                        <select id="create-billable_scope" name="billable_scope" class="form-control">
                            <option value="both" @selected(old('billable_scope') === 'both')>{{ __('User & Team') }}</option>
                            <option value="user" @selected(old('billable_scope') === 'user')>{{ __('User only') }}</option>
                            <option value="team" @selected(old('billable_scope') === 'team')>{{ __('Team only') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">{{ __('Included tools') }}</label>
                    <div class="mt-2 flex flex-wrap gap-3">
                        @foreach ($modules as $module)
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                <input type="checkbox" name="modules[]" value="{{ $module->id }}" @checked(collect(old('modules', []))->contains($module->id)) class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                                {{ $module->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <input type="hidden" name="is_active" value="1">

                <button type="submit" class="btn btn-primary w-full">{{ __('Create bundle') }}</button>
            </form>
        </div>
    </div>
@endsection
