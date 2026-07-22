@extends('layouts.shell')

@section('title', ($productType->id ?? false) ? __('Edit Product Type') : __('Add Product Type'))

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ ($productType->id ?? false) ? __('Edit Product Type') : __('Add Product Type') }}</h1>

        <form method="POST" action="{{ ($productType->id ?? false) ? route('dentaltrack.admin.product-types.update', $productType) : route('dentaltrack.admin.product-types.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            @if($productType->id ?? false) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Company') }}</label>
                <select name="dentaltrack_company_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">{{ __('Select company') }}</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" {{ old('dentaltrack_company_id', $productType->dentaltrack_company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $productType->name) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" rows="3">{{ old('description', $productType->description) }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $productType->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-700">{{ __('Active') }}</span>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('dentaltrack.admin.product-types.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
