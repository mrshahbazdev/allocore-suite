@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.webhooks.edit_title', ['name' => $webhook->name]) }}</h1>
            <p class="text-sm text-slate-500">{{ $webhook->integration->name }}</p>
        </div>
        <a href="{{ route('admin.webhooks.history', $webhook) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('History') }}</a>
    </div>

    <div class="max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.webhooks.update', $webhook) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('admin.webhooks._form')

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.integrations.edit', $webhook->integration_id) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.webhooks.save_button') }}</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.webhooks.destroy', $webhook) }}" onsubmit="return confirm('{{ __('admin.webhooks.confirm_delete') }}')" class="mt-4">
            @csrf
            @method('DELETE')
            <button class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
        </form>
    </div>
@endsection
