@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
    <input name="title" value="{{ old('title', $announcement->title ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Body') }}</label>
    <textarea name="body" rows="4" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('body', $announcement->body ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
    <select name="type" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach (['info' => 'Info', 'warning' => 'Warning', 'success' => 'Success'] as $key => $label)
            <option value="{{ $key }}" @selected(old('type', $announcement->type ?? 'info') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.announcements.starts_at') }}</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($announcement) && $announcement->starts_at ? $announcement->starts_at->format('Y-m-d\TH:i') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.announcements.ends_at') }}</label>
        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($announcement) && $announcement->ends_at ? $announcement->ends_at->format('Y-m-d\TH:i') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
</div>

<div class="flex items-center gap-2">
    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $announcement->is_active ?? true))>
    <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
</div>
