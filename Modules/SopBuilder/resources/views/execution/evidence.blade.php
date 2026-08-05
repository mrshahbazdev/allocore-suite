@php $title = __('Upload Evidence'); @endphp
@extends('layouts.shell')

@section('content')
<div class="max-w-2xl mx-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Upload Evidence') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ $sop->title }}</p>

    <form method="POST" action="{{ route('sopbuilder.evidence.store', $sop) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('File') }}</label>
            <input type="file" name="file" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('sopbuilder.sops.show', $sop) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
            <button type="submit" class="rounded-lg bg-[#ff9200] px-6 py-2 text-sm font-semibold text-white hover:bg-orange-600">{{ __('Upload') }}</button>
        </div>
    </form>
</div>
@endsection
