@extends('layouts.shell', ['title' => __('Edit Role')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('Edit Role') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('orgmatrix.organizations.roles.update', [$organization, $role]) }}" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ $role->name }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Department') }}</label>
                <input type="text" name="department" value="{{ $role->department }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Reports to') }}</label>
                <select name="parent_role_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    <option value="">—</option>
                    @foreach ($parentRoles as $parent)
                        <option value="{{ $parent->id }}" {{ $role->parent_role_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Criticality') }}</label>
                <select name="criticality" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    @foreach (Modules\OrgMatrix\Models\Role::CRITICALITIES as $level)
                        <option value="{{ $level }}" {{ $role->criticality === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $role->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                <label class="text-sm text-slate-700">{{ __('Active') }}</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Sort order') }}</label>
                <input type="number" name="sort_order" value="{{ $role->sort_order }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $role->description }}</textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('orgmatrix.organizations.roles.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
