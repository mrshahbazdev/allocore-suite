@extends('layouts.shell', ['title' => __('New Delegation')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('New Delegation') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <form method="POST" action="{{ route('focusmatrix.delegations.store') }}" class="space-y-4" id="delegation-form">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Task') }}</label>
                <select name="task_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    @foreach ($tasks as $t)
                        <option value="{{ $t->id }}" {{ ($task->id ?? null) == $t->id ? 'selected' : '' }}>{{ $t->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Delegate (team member)') }}</label>
                <select name="delegate_user_id" id="delegate_user_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    <option value="">—</option>
                    @foreach ($candidates as $candidate)
                        <option value="{{ $candidate['id'] }}">{{ $candidate['name'] }} ({{ $candidate['email'] }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Or external name') }}</label>
                <input type="text" name="delegate_name_fallback" id="delegate_name_fallback" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Goal') }}</label>
                <textarea name="goal" id="goal" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required></textarea>
                <button type="button" onclick="aiDraft()" class="mt-2 text-sm text-indigo-600 hover:underline">{{ __('AI draft') }}</button>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Decision scope') }}</label>
                    <select name="decision_scope" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                        @foreach (Modules\FocusMatrix\Models\Delegation::SCOPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Deadline') }}</label>
                    <input type="date" name="deadline" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Resources') }}</label>
                <textarea name="resources" id="resources" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="no_micromanagement" value="0">
                <input type="checkbox" name="no_micromanagement" value="1" checked id="no_micromanagement" class="rounded border-slate-300 text-indigo-600">
                <label for="no_micromanagement" class="text-sm text-slate-700">{{ __('No micromanagement') }}</label>
            </div>
            <div class="flex justify-end">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Delegate') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
async function aiDraft() {
    const taskId = document.querySelector('select[name=task_id]').value;
    const name = document.getElementById('delegate_name_fallback').value || document.getElementById('delegate_user_id')?.selectedOptions?.[0]?.text;
    if (!taskId) return;
    const res = await fetch('{{ route('focusmatrix.delegations.ai-draft') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ task_id: taskId, delegate_name: name })
    });
    const data = await res.json();
    if (data.ok) {
        document.getElementById('goal').value = data.draft.goal || '';
        if (data.draft.decision_scope) document.querySelector('select[name=decision_scope]').value = data.draft.decision_scope;
        document.getElementById('resources').value = data.draft.resources || '';
    } else {
        alert(data.message || '{{ __('AI not available') }}');
    }
}
</script>
@endsection
