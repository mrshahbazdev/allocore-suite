@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
    <input name="name" value="{{ old('name', $taxRate->name ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.tax_rates.rate') }} (%)</label>
    <input name="rate" type="number" step="0.0001" value="{{ old('rate', $taxRate->rate ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Country') }}</label>
        <input name="country" value="{{ old('country', $taxRate->country ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Region') }}</label>
        <input name="region" value="{{ old('region', $taxRate->region ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
</div>

<div class="flex items-center gap-4">
    <div class="flex items-center gap-2">
        <input id="is_default" name="is_default" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_default', $taxRate->is_default ?? false))>
        <label for="is_default" class="text-sm font-medium text-slate-700">{{ __('Default') }}</label>
    </div>
    <div class="flex items-center gap-2">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $taxRate->is_active ?? true))>
        <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
    </div>
</div>
