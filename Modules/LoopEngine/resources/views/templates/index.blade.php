@extends('layouts.shell')

@section('title', __('Template Marketplace'))
@section('page-title', __('Template Marketplace'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Template Marketplace') }}</h1>

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-wrap items-end gap-3">
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Search') }}</label><input type="text" name="search" value="{{ request('search') }}" class="mt-1 rounded-lg border-slate-300"></div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
                <select name="category" class="mt-1 rounded-lg border-slate-300">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($templates as $template)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="font-semibold text-slate-900">{{ $template->localizedName() }}</div>
                    <div class="mt-1 text-sm text-slate-500">{{ Str::limit($template->localizedDescription(), 120) }}</div>
                    <div class="mt-2 text-xs text-slate-500">{{ $template->category }} — {{ $template->install_count }} {{ __('installs') }} — {{ number_format($template->rating, 1) }} / 5</div>
                    <a href="{{ route('loopengine.templates.show', $template) }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Details') }}</a>
                </div>
            @endforeach
        </div>
        <div>{{ $templates->links() }}</div>
    </div>
@endsection
