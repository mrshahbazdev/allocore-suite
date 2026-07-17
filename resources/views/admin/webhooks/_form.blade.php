@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
    <input name="name" value="{{ old('name', $webhook->name ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">URL</label>
    <input name="url" type="url" value="{{ old('url', $webhook->url ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.webhooks.events') }}</label>
    <input name="events" value="{{ old('events', isset($webhook) ? implode(', ', $webhook->events ?? []) : '') }}" placeholder="user.created, invoice.paid" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    <p class="mt-1 text-xs text-slate-500">{{ __('admin.webhooks.events_help') }}</p>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.webhooks.secret') }}</label>
    <input name="secret" value="{{ old('secret', $webhook->secret ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="flex items-center gap-2">
    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $webhook->is_active ?? true))>
    <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
</div>
