@extends('layouts.shell')

@section('title', $employee->exists ? __('Edit Employee') : __('New Employee'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $employee->exists ? __('Edit Employee') : __('New Employee') }}</h1>
        <form method="POST" action="{{ $employee->exists ? route('smartkpi.employees.update', $employee) : route('smartkpi.departments.employees.store', $department) }}" class="mt-6 space-y-4">
            @csrf
            @if ($employee->exists) @method('PUT') @endif

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $employee->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label><input type="email" name="email" value="{{ old('email', $employee->email) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('User') }}</label>
                <select name="user_id" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Role') }}</label><input type="text" name="role" value="{{ old('role', $employee->role) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Active') }}</span></div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
