@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
    <input name="name" value="{{ old('name', $template->name ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Slug') }}</label>
    <input name="slug" value="{{ old('slug', $template->slug ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
    <textarea name="description" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $template->description ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Team') }}</label>
    <select name="team_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        @foreach ($teams as $team)
            <option value="{{ $team->id }}" @selected(old('team_id', $template->team_id ?? '') == $team->id)>{{ $team->name }}</option>
        @endforeach
    </select>
</div>

<div class="flex items-center gap-2">
    <input id="is_default" name="is_default" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_default', $template->is_default ?? false))>
    <label for="is_default" class="text-sm font-medium text-slate-700">{{ __('admin.audit_templates.is_default') }}</label>
</div>
