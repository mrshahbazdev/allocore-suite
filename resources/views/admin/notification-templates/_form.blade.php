@csrf

<div class="grid gap-5 md:grid-cols-3">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Key') }}</label>
        <input name="key" value="{{ old('key', $notificationTemplate->key ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Locale') }}</label>
        <input name="locale" value="{{ old('locale', $notificationTemplate->locale ?? 'en') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
        <select name="type" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="email" @selected(old('type', $notificationTemplate->type ?? 'email') === 'email')>{{ __('Email') }}</option>
            <option value="in_app" @selected(old('type', $notificationTemplate->type ?? '') === 'in_app')>{{ __('In-App') }}</option>
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Subject') }}</label>
    <input name="subject" value="{{ old('subject', $notificationTemplate->subject ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Body') }}</label>
    <textarea name="body" rows="6" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('body', $notificationTemplate->body ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.notification_templates.variables') }}</label>
    <input name="variables" value="{{ old('variables', isset($notificationTemplate) ? implode(', ', $notificationTemplate->variables ?? []) : '') }}" placeholder="name, email, team_name" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    <p class="mt-1 text-xs text-slate-500">{{ __('admin.notification_templates.variables_help') }}</p>
</div>

<div class="flex items-center gap-2">
    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $notificationTemplate->is_active ?? true))>
    <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
</div>
