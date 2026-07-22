@extends('layouts.shell')

@section('title', __('Import Transactions'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Import CSV') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('Upload a CSV with columns: date, description, amount, type (income/expense).') }}</p>
        <form method="POST" action="{{ route('cashcore.transactions.import.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <div><input type="file" name="csv_file" accept=".csv,.txt" class="block w-full text-sm text-slate-700 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-indigo-700" required></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Import') }}</button>
        </form>
    </div>
@endsection
