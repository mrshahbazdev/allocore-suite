@php $title = __('SOPs'); @endphp
@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('SOP Builder') }}</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Standard Operating Procedures') }}</h1>
        </div>
        <a href="{{ route('sopbuilder.sops.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">{{ __('New SOP') }}</a>
    </div>

    <form method="GET" action="{{ route('sopbuilder.sops.index') }}" class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search SOPs...') }}" class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
        <select name="status" class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
            <option value="">{{ __('All statuses') }}</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>{{ __('Archived') }}</option>
        </select>
        <select name="category" class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
            <option value="">{{ __('All categories') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">{{ __('Filter') }}</button>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Title') }}</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Category') }}</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Version') }}</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-700">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sops as $sop)
                    <tr class="border-t">
                        <td class="px-4 py-3">
                            <a href="{{ route('sopbuilder.sops.show', $sop) }}" class="font-medium text-slate-900 hover:text-[#ff9200]">{{ $sop->title }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $sop->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $sop->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($sop->status === 'archived' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700') }}">{{ $sop->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $sop->version }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('sopbuilder.sops.show', $sop) }}" class="text-[#0094af] hover:underline">{{ __('View') }}</a>
                                <a href="{{ route('sopbuilder.sops.edit', $sop) }}" class="text-[#ff9200] hover:underline">{{ __('Edit') }}</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">{{ __('No SOPs found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
