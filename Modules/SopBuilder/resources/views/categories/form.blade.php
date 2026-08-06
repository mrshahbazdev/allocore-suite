@php $title = $category ? __('Edit Category') : __('New Category'); @endphp
@extends('layouts.shell')

@section('content')
<div class="max-w-2xl mx-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
    <form method="POST" action="{{ $category ? route('sopbuilder.categories.update', $category) : route('sopbuilder.categories.store') }}" class="mt-6 space-y-4">
        @csrf
        @if($category) @method('PUT') @endif
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $category?->name) }}" required class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Color') }}</label>
                <input type="color" name="color" value="{{ old('color', $category?->color ?? '#ff9200') }}" class="mt-1 block h-10 w-full rounded-lg border-slate-300 p-1 focus:border-[#ff9200] focus:ring-[#ff9200]">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Icon') }}</label>
                <input type="text" name="icon" value="{{ old('icon', $category?->icon) }}" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('sopbuilder.categories.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
            <button type="submit" class="rounded-lg bg-[#ff9200] px-6 py-2 text-sm font-semibold text-white hover:bg-orange-600">{{ $category ? __('Update') : __('Create') }}</button>
        </div>
    </form>
</div>
@endsection
