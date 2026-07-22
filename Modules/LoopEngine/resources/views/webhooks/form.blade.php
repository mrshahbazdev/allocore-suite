@extends('layouts.shell')

@section('title', $webhook->exists ? __('Edit Webhook') : __('New Webhook'))
@section('page-title', $webhook->exists ? __('Edit Webhook') : __('New Webhook'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $webhook->exists ? __('Edit Webhook') : __('New Webhook') }}</h1>
        <form method="POST" action="{{ $webhook->exists ? route('loopengine.webhooks.update', $webhook) : route('loopengine.webhooks.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($webhook->exists)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label><input type="text" name="name" value="{{ old('name', $webhook->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('URL') }}</label><input type="url" name="url" value="{{ old('url', $webhook->url) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
            </div>

            <div><label class="block text-sm font-medium text-slate-700">{{ __('Secret') }}</label><input type="text" name="secret" value="{{ old('secret', $webhook->secret) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Events') }}</label>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach (Modules\LoopEngine\Http\Controllers\WebhookController::events() as $event)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="events[]" value="{{ $event }}" {{ in_array($event, old('events', $webhook->events ?? [])) ? 'checked' : '' }} class="rounded border-slate-300">
                            {{ $event }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $webhook->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Active') }}</span></div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
