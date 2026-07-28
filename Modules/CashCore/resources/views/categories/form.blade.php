@extends('layouts.shell')

@section('title', $category->exists ? __('Edit Category') : __('New Category'))

@section('content')
    <div class="space-y-6 max-w-xl">
        <h1 class="text-2xl font-bold text-slate-900">{{ $category->exists ? __('Edit Category') : __('New Category') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ $category->exists ? route('cashcore.categories.update', $category) : route('cashcore.categories.store') }}" class="space-y-4">
                @csrf
                @if ($category->exists) @method('PUT') @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $category->name) }}" class="mt-1 w-full rounded-lg  focus:border-indigo-500 focus:ring-indigo-500" required></div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                        <select name="type" class="mt-1 w-full rounded-lg  focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (['income' => 'Income', 'expense' => 'Expense'] as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $category->type) === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Icon') }}</label><input type="text" name="icon" value="{{ old('icon', $category->icon) }}" class="mt-1 w-full rounded-lg  focus:border-indigo-500 focus:ring-indigo-500" placeholder="💰"></div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Color') }}</label><input type="text" name="color" value="{{ old('color', $category->color) }}" class="mt-1 w-full rounded-lg  focus:border-indigo-500 focus:ring-indigo-500" placeholder="#22c55e"></div>
                </div>

                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </form>
        </div>
    </div>
@endsection
