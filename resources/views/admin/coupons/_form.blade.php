@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Code') }}</label>
    <input name="code" value="{{ old('code', $coupon->code ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.coupons.type') }}</label>
    <select name="type" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="percent" @selected(old('type', $coupon->type ?? 'percent') === 'percent')>{{ __('Percent') }}</option>
        <option value="fixed" @selected(old('type', $coupon->type ?? '') === 'fixed')>{{ __('Fixed') }}</option>
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.coupons.value') }}</label>
    <input name="value" type="number" step="0.01" value="{{ old('value', $coupon->value ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.coupons.max_uses') }}</label>
    <input name="max_uses" type="number" value="{{ old('max_uses', $coupon->max_uses ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.coupons.starts_at') }}</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($coupon) && $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.coupons.expires_at') }}</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
    <textarea name="description" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $coupon->description ?? '') }}</textarea>
</div>

<div class="flex items-center gap-2">
    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $coupon->is_active ?? true))>
    <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
</div>
