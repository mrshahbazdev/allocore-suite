@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.log_viewer.title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('admin.log_viewer.description') }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-4">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-1">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('admin.log_viewer.files') }}</div>
            <div class="max-h-96 overflow-y-auto">
                @forelse ($logs as $log)
                    <a href="{{ route('admin.log-viewer.index', ['file' => $log['name']]) }}" class="block border-b border-slate-100 px-4 py-3 text-sm {{ $current === $log['name'] ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="flex items-center justify-between">
                            <span>{{ $log['name'] }}</span>
                            <span class="text-xs text-slate-400">{{ number_format($log['size'] / 1024, 1) }} KB</span>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-slate-400">{{ __('admin.log_viewer.empty') }}</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-3">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $current ?? __('admin.log_viewer.no_file') }}</div>
            <pre class="max-h-[600px] overflow-auto p-4 text-xs text-slate-700">{{ $content ?: __('admin.log_viewer.no_content') }}</pre>
        </div>
    </div>
@endsection
