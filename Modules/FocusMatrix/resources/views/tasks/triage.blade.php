@extends('layouts.shell', ['title' => __('Triage')])

@section('content')
<div class="max-w-xl mx-auto">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm text-center space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $task->title }}</h1>
        <p class="text-slate-500">{{ __('Can only you really do this?') }}</p>

        <form method="POST" action="{{ route('focusmatrix.tasks.triage.decide', $task) }}">
            @csrf
            <input type="hidden" name="answer" id="answer" value="yes">
            <div id="category-box" class="mb-4 text-left">
                <label class="block text-sm font-medium text-slate-700">{{ __('Only-You Category') }}</label>
                <select name="only_you_category" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    @foreach (Modules\FocusMatrix\Models\Task::CATEGORIES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <button type="submit" onclick="document.getElementById('answer').value='yes'" class="rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Yes — Keep') }}</button>
                <button type="submit" onclick="document.getElementById('answer').value='no'" class="rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('No — Delegate') }}</button>
                <button type="button" onclick="askAi()" class="rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-500">{{ __('Maybe — Ask AI') }}</button>
            </div>
        </form>

        <div id="ai-result" class="hidden rounded-lg bg-slate-50 p-4 text-left text-sm text-slate-700"></div>
    </div>
</div>

<script>
async function askAi() {
    const result = document.getElementById('ai-result');
    result.classList.remove('hidden');
    result.textContent = '{{ __('Thinking...') }}';
    try {
        const res = await fetch('{{ route('focusmatrix.tasks.ai-suggest', $task) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
        });
        const data = await res.json();
        result.innerHTML = `<strong>${data.label}</strong> (${data.source}, ${Math.round(data.confidence * 100)}%)<br>${data.rationale}`;
    } catch (e) {
        result.textContent = '{{ __('AI not available.') }}';
    }
}
</script>
@endsection
