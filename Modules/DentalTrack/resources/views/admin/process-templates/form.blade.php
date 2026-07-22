@extends('layouts.shell')

@section('title', ($processTemplate->id ?? false) ? __('Edit Step') : __('Add Step'))

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ ($processTemplate->id ?? false) ? __('Edit Step') : __('Add Step') }}</h1>

        <form method="POST" action="{{ ($processTemplate->id ?? false) ? route('dentaltrack.admin.process-templates.update', $processTemplate) : route('dentaltrack.admin.process-templates.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            @csrf
            @if($processTemplate->id ?? false) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Product Type') }}</label>
                <select name="dentaltrack_product_type_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">{{ __('Select product type') }}</option>
                    @foreach ($productTypes as $pt)
                        <option value="{{ $pt->id }}" {{ old('dentaltrack_product_type_id', $processTemplate->dentaltrack_product_type_id) == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Sort Order') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $processTemplate->sort_order) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="1" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Step Name') }}</label>
                <input type="text" name="step_name" value="{{ old('step_name', $processTemplate->step_name) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Expected Minutes') }}</label>
                <input type="number" name="expected_minutes" value="{{ old('expected_minutes', $processTemplate->expected_minutes) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="0">
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('dentaltrack.admin.process-templates.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
@endsection
