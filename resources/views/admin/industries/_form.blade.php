@php($industry = $industry ?? null)

<div class="space-y-5">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
        <input id="name" name="name" type="text" value="{{ old('name', $industry?->name) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
    </div>

    <div>
        <label for="parent_id" class="block text-sm font-medium text-slate-700">{{ __('Parent cluster') }}</label>
        <select id="parent_id" name="parent_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('Top-level cluster') }}</option>
            @foreach ($clusters as $cluster)
                @continue($industry && $cluster->id === $industry->id)
                <option value="{{ $cluster->id }}" {{ old('parent_id', $industry?->parent_id) == $cluster->id ? 'selected' : '' }}>{{ $cluster->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">{{ __('Select a parent to make this a sub-industry. Leave empty for a cluster.') }}</p>
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-slate-700">{{ __('Slug') }}</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $industry?->slug) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <p class="mt-1 text-xs text-slate-500">{{ __('Leave empty to generate from name.') }}</p>
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700">{{ __('Sort order') }}</label>
        <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $industry?->sort_order ?? 0) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="flex items-center gap-2">
        <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $industry?->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
    </div>
</div>
