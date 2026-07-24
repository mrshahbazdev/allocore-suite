@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Bulk Import') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Upload a CSV or Excel file to import records into a module.') }}</p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if (session('import_errors') && count(session('import_errors')))
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
            <p class="font-medium">{{ __('Some rows could not be imported:') }}</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('imports.upload') }}" enctype="multipart/form-data" class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Module') }}</label>
            <select name="module_key" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                <option value="">{{ __('Select a module...') }}</option>
                @foreach ($modules as $module)
                    <option value="{{ $module->key }}">{{ $module->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('File') }}</label>
            <input type="file" name="file" accept=".csv,.xlsx" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
            <p class="mt-1 text-xs text-slate-500">{{ __('Supported formats: CSV, XLSX (max 5 MB).') }}</p>
        </div>

        <div class="pt-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Upload and map columns') }}</button>
        </div>
    </form>
@endsection
