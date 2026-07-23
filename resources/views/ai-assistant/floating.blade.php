@auth
<div x-data="{ open: false, loading: false }" class="fixed bottom-4 right-4 z-50">
    <button @click="open = !open" class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg hover:bg-indigo-700 focus:outline-none" aria-label="{{ __('AI Assistant') }}">
        <svg x-show="!open" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L1.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
        <svg x-show="open" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    <div x-show="open" x-cloak class="absolute bottom-16 right-0 w-80 sm:w-96 rounded-xl border border-slate-200 bg-white shadow-2xl" style="display: none;">
        <div class="flex items-center justify-between rounded-t-xl bg-indigo-600 px-4 py-3 text-white">
            <span class="font-medium">{{ __('AI Assistant') }}</span>
            <a href="{{ route('assistant.index') }}" class="text-xs underline">{{ __('Open full') }}</a>
        </div>
        <div class="h-80 flex flex-col">
            <div id="ai-messages" class="flex-1 overflow-y-auto p-4 space-y-3 text-sm">
                <div class="rounded-lg bg-slate-100 p-3 text-slate-700">
                    {{ __('Hi! Ask me about your tools, workflows, or what to do next.') }}
                </div>
            </div>
            <form method="POST" action="{{ route('assistant.store') }}" class="border-t border-slate-200 p-3" @submit.prevent="
                loading = true;
                const input = $event.target.message;
                const formData = new FormData($event.target);
                fetch($event.target.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: formData
                }).then(r => r.json()).then(data => {
                    const box = document.getElementById('ai-messages');
                    const bubble = (text, cls) => `<div class='rounded-lg p-3 ${cls}'>${text}</div>`;
                    box.innerHTML += bubble(input.value.replace(/</g, '&lt;'), 'bg-indigo-600 text-white ml-8');
                    box.innerHTML += bubble(data.reply, 'bg-slate-100 text-slate-700 mr-8');
                    input.value = '';
                    box.scrollTop = box.scrollHeight;
                    loading = false;
                }).catch(() => loading = false);
            ">
                @csrf
                <input type="hidden" name="module_key" value="{{ Route::currentRouteName() ? explode('.', Route::currentRouteName())[0] : null }}">
                <input type="hidden" name="page_url" value="{{ request()->url() }}">
                <div class="flex gap-2">
                    <input type="text" name="message" placeholder="{{ __('Ask something...') }}" required class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <button type="submit" :disabled="loading" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">{{ __('Send') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth
