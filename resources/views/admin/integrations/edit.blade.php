@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.integrations.edit_title', ['name' => $integration->name]) }}</h1>
            <p class="text-sm text-slate-500">{{ $integration->type }}</p>
        </div>
        <a href="{{ route('admin.integrations.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to integrations') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.integrations.update', $integration) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('admin.integrations._form')

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.integrations.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.integrations.save_button') }}</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.integrations.destroy', $integration) }}" onsubmit="return confirm('{{ __('admin.integrations.confirm_delete') }}')" class="mt-4">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('admin.webhooks.title') }}</h2>
                    <a href="{{ route('admin.webhooks.create', ['integration_id' => $integration->id]) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">{{ __('admin.webhooks.add') }}</a>
                </div>
                <div class="space-y-3">
                    @forelse ($integration->webhooks as $webhook)
                        <div class="rounded-lg border border-slate-200 p-3 text-sm">
                            <div class="font-medium text-slate-900">{{ $webhook->name }}</div>
                            <div class="text-xs text-slate-500 truncate">{{ $webhook->url }}</div>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $webhook->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $webhook->is_active ? __('Active') : __('Inactive') }}</span>
                                <a href="{{ route('admin.webhooks.edit', $webhook) }}" class="text-xs font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.webhooks.destroy', $webhook) }}" onsubmit="return confirm('{{ __('admin.webhooks.confirm_delete') }}')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs font-medium text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('admin.webhooks.empty') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
