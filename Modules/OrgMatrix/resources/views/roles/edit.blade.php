@extends('layouts.shell', ['title' => __('Edit Role')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('orgmatrix.organizations.roles.index', $organization) }}" class="hover:text-indigo-600">{{ __('Roles') }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ $role->name }}</span>
    </div>

    <h1 class="text-3xl font-bold text-slate-900">{{ __('Edit Role') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('orgmatrix.organizations.roles.update', [$organization, $role]) }}" class="space-y-5">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ $role->name }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Department') }}</label>
                    <input type="text" name="department" value="{{ $role->department }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Criticality') }}</label>
                    <select name="criticality" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (Modules\OrgMatrix\Models\Role::CRITICALITIES as $level)
                            <option value="{{ $level }}" {{ $role->criticality === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Reports to') }}</label>
                    <select name="parent_role_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— {{ __('None (top-level)') }}</option>
                        @foreach ($parentRoles as $parent)
                            <option value="{{ $parent->id }}" {{ $role->parent_role_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Sort order') }}</label>
                    <input type="number" name="sort_order" value="{{ $role->sort_order }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $role->description }}</textarea>
            </div>
            <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $role->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('orgmatrix.organizations.roles.index', $organization) }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update Role') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
