@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('AI Assistant') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Ask about your modules, get next-step recommendations, and navigate the platform.') }}</p>
        </div>
        <form method="POST" action="{{ route('assistant.destroy') }}" onsubmit="return confirm('{{ __("Clear chat history?") }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Clear history') }}</button>
        </form>
    </div>

    <div class="flex h-[60vh] flex-col rounded-xl border border-slate-200 bg-white shadow-sm">
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4">
            @forelse ($messages as $msg)
                <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm {{ $msg->role === 'user' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-800' }}">
                        {{ $msg->content }}
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-slate-400">{{ __('Start a conversation with the assistant.') }}</p>
            @endforelse
        </div>

        @php($aiModuleKey = \App\Services\AiAssistantContext::currentModuleKey())
        @if ($aiModuleKey)
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-500">
                {{ __('Context') }}: {{ \App\Models\Module::where('key', $aiModuleKey)->value('name') ?? $aiModuleKey }}
            </div>
        @endif

        <form method="POST" action="{{ route('assistant.store') }}" class="border-t border-slate-200 p-4">
            @csrf
            <input type="hidden" name="module_key" value="{{ $aiModuleKey }}">
            <input type="hidden" name="page_url" value="{{ request()->url() }}">
            <div class="flex gap-2">
                <input type="text" name="message" placeholder="{{ __('Type your question...') }}" required class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none" autofocus>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Send') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
