@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.media.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.media.description') }}</p>
        </div>
    </div>

    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <input type="file" name="files[]" multiple class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700">
                </div>
                <div>
                    <input type="text" name="collection" value="{{ request('collection', 'default') }}" placeholder="{{ __('admin.media.collection') }}" class="block w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.media.upload_button') }}</button>
        </form>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.media.index') }}" class="flex gap-2 p-4">
            <select name="collection" class="rounded-lg border-slate-300 text-sm">
                <option value="">{{ __('admin.media.all_collections') }}</option>
                @foreach ($collections as $collection)
                    <option value="{{ $collection }}" @selected(request('collection') === $collection)>{{ $collection }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.media.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($media as $item)
                <div class="rounded-xl border border-slate-200 p-3">
                    @if (Str::startsWith($item->mime_type, 'image/'))
                        <img src="{{ $item->url() }}" alt="" class="mb-2 h-32 w-full rounded-lg object-cover">
                    @else
                        <div class="mb-2 flex h-32 items-center justify-center rounded-lg bg-slate-50 text-slate-400 text-sm">{{ $item->mime_type }}</div>
                    @endif
                    <div class="truncate text-sm font-medium text-slate-900" title="{{ $item->file_name }}">{{ $item->file_name }}</div>
                    <div class="text-xs text-slate-500">{{ $item->collection }} · {{ number_format($item->size / 1024, 1) }} KB</div>
                    <div class="mt-2 flex items-center gap-2">
                        <a href="{{ $item->url() }}" target="_blank" class="text-xs font-medium text-indigo-600 hover:underline">{{ __('View') }}</a>
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('{{ __('admin.media.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs font-medium text-rose-600 hover:underline">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-6 text-center text-sm text-slate-400">{{ __('admin.media.empty') }}</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">{{ $media->links() }}</div>
@endsection
