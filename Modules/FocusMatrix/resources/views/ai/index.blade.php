@extends('layouts.shell', ['title' => __('AI Settings')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('AI Settings') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <form method="POST" action="{{ route('focusmatrix.ai.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Provider') }}</label>
                <select name="provider" id="provider" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    @foreach (Modules\FocusMatrix\Models\AiSetting::PROVIDERS as $key => $label)
                        <option value="{{ $key }}" {{ ($setting['provider'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('API Key') }}</label>
                <input type="password" name="api_key" placeholder="{{ $setting['masked_key'] ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Model') }}</label>
                <select name="model" id="model" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    <option value="">{{ __('Default') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Monthly limit') }}</label>
                <input type="number" name="monthly_limit" value="{{ $setting['monthly_limit'] ?? 200 }}" min="10" max="10000" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="enabled" value="0">
                <input type="checkbox" name="enabled" value="1" {{ ($setting['enabled'] ?? true) ? 'checked' : '' }} id="enabled" class="rounded border-slate-300 text-indigo-600">
                <label for="enabled" class="text-sm text-slate-700">{{ __('Enabled') }}</label>
            </div>
            @if ($setting)
                <div class="text-sm text-slate-500">{{ __('Used this month') }}: {{ $setting['calls_this_month'] }} / {{ $setting['monthly_limit'] }}</div>
            @endif
            <div class="flex items-center gap-3">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
                <button type="button" onclick="testAi()" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Test') }}</button>
            </div>
        </form>
        <div id="test-result" class="hidden rounded-lg bg-slate-50 p-3 text-sm text-slate-700"></div>
    </div>
</div>

<script>
const models = @json($models);
const defaults = @json($default_models);
function updateModels() {
    const p = document.getElementById('provider').value;
    const sel = document.getElementById('model');
    sel.innerHTML = '<option value="">{{ __('Default') }}</option>';
    (models[p] || []).forEach(m => {
        const opt = document.createElement('option');
        opt.value = m;
        opt.textContent = m;
        sel.appendChild(opt);
    });
}
document.getElementById('provider').addEventListener('change', updateModels);
updateModels();
async function testAi() {
    const key = document.querySelector('input[name=api_key]').value;
    const provider = document.getElementById('provider').value;
    const model = document.getElementById('model').value;
    const box = document.getElementById('test-result');
    if (!key) { box.textContent = '{{ __('Enter an API key to test') }}'; box.classList.remove('hidden'); return; }
    box.classList.remove('hidden');
    box.textContent = '{{ __('Testing...') }}';
    const res = await fetch('{{ route('focusmatrix.ai.test') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ provider, api_key: key, model })
    });
    const data = await res.json();
    box.textContent = data.ok ? '{{ __('Connected') }}' : data.message;
}
</script>
@endsection
