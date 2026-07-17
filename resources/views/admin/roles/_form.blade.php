@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
    <input name="name" value="{{ old('name', $role->name ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.roles.guard') }}</label>
    <input name="guard_name" value="{{ old('guard_name', $role->guard_name ?? 'web') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('admin.roles.permissions') }}</label>
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($permissions as $permission)
            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    @checked(in_array($permission->id, old('permissions', isset($role) ? $role->permissions->pluck('id')->toArray() : [])))>
                <span class="text-slate-700">{{ $permission->name }}</span>
            </label>
        @endforeach
    </div>
</div>
