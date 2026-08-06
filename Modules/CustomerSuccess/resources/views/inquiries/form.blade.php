@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ __('Ask Customer Success Assistant') }}</h1>

    <form method="POST" action="{{ route('customersuccess.inquiries.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Question') }}</label>
            <textarea name="question" rows="4" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]" placeholder="{{ __('e.g. How do I read a red risk rating in my audit report?') }}" required>{{ old('question') }}</textarea>
        </div>

        <input type="hidden" name="module_key" value="{{ request('module_key', old('module_key')) }}">

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Ask') }}</button>
            <a href="{{ route('customersuccess.dashboard') }}" class="text-sm text-slate-600 hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
