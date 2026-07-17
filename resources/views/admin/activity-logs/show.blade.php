@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.activity_logs.title') }}</h1>
            <p class="text-sm text-slate-500">{{ $activityLog->log_name }}</p>
        </div>
        <a href="{{ route('admin.activity-logs.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to logs') }}</a>
    </div>

    <div class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between border-b border-slate-100 pb-2"><dt class="text-slate-500">{{ __('admin.activity_logs.time') }}</dt><dd class="font-medium text-slate-900">{{ $activityLog->created_at->format('d.m.Y H:i:s') }}</dd></div>
            <div class="flex justify-between border-b border-slate-100 pb-2"><dt class="text-slate-500">{{ __('admin.activity_logs.type') }}</dt><dd class="font-medium text-slate-900">{{ $activityLog->log_name }}</dd></div>
            <div class="flex justify-between border-b border-slate-100 pb-2"><dt class="text-slate-500">{{ __('Causer') }}</dt><dd class="font-medium text-slate-900">{{ $activityLog->causer?->name ?? '—' }}</dd></div>
            <div class="flex justify-between border-b border-slate-100 pb-2"><dt class="text-slate-500">{{ __('Team') }}</dt><dd class="font-medium text-slate-900">{{ $activityLog->team?->name ?? '—' }}</dd></div>
            <div class="flex justify-between border-b border-slate-100 pb-2"><dt class="text-slate-500">{{ __('admin.activity_logs.subject') }}</dt><dd class="font-medium text-slate-900">{{ $activityLog->subject_type ? class_basename($activityLog->subject_type).' #'.$activityLog->subject_id : '—' }}</dd></div>
        </dl>

        <div class="mt-6">
            <h3 class="text-sm font-semibold text-slate-900">{{ __('admin.activity_logs.description') }}</h3>
            <p class="mt-2 text-sm text-slate-700">{{ $activityLog->description }}</p>
        </div>

        @if ($activityLog->properties)
            <div class="mt-6">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('admin.activity_logs.properties') }}</h3>
                <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>
@endsection
