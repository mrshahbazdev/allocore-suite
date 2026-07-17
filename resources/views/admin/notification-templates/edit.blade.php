@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.notification_templates.edit_title', ['key' => $notificationTemplate->key]) }}</h1>
    </div>

    <div class="max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.notification-templates.update', $notificationTemplate) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('admin.notification-templates._form')

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.notification-templates.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.notification_templates.save_button') }}</button>
            </div>
        </form>
    </div>
@endsection
