@php
$config = old('config', isset($integration) ? $integration->config : []);
if (! is_array($config) || empty($config)) {
    $config = [['key' => '', 'value' => '']];
}
@endphp

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
    <input name="name" value="{{ old('name', $integration->name ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
    <input name="type" value="{{ old('type', $integration->type ?? '') }}" placeholder="e.g. stripe, mailchimp, webhook" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
    <textarea name="description" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $integration->description ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('admin.integrations.config') }}</label>
    <div x-data="{ rows: {{ Js::from($config) }} }" class="space-y-2">
        <template x-for="(row, index) in rows" :key="index">
            <div class="flex gap-2">
                <input type="text" :name="`config[${index}][key]`" x-model="row.key" placeholder="{{ __('Key') }}" class="flex-1 rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <input type="text" :name="`config[${index}][value]`" x-model="row.value" placeholder="{{ __('Value') }}" class="flex-1 rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="button" @click="rows.splice(index, 1)" class="rounded-lg border border-rose-300 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Remove') }}</button>
            </div>
        </template>
        <button type="button" @click="rows.push({key: '', value: ''})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">+ {{ __('admin.integrations.add_config') }}</button>
    </div>
</div>

<div class="flex items-center gap-2">
    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $integration->is_active ?? true))>
    <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
</div>
