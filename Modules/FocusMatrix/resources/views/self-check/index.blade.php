@extends('layouts.shell', ['title' => __('Weekly Self-Check')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Weekly Self-Check') }} — {{ __('Week') }} {{ $now->weekOfYear }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <form method="POST" action="{{ route('focusmatrix.self-check.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('What did others do that I should have delegated earlier?') }}</label>
                <textarea name="q1_others_could_do" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $current?->q1_others_could_do }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('What did I delegate too late?') }}</label>
                <textarea name="q2_delegated_late" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $current?->q2_delegated_late }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('What will I omit next week?') }}</label>
                <textarea name="q3_to_omit_next_week" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $current?->q3_to_omit_next_week }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Which decisions needed my unique contribution?') }}</label>
                <textarea name="q4_focused_decisions" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $current?->q4_focused_decisions }}</textarea>
            </div>
            <div class="flex items-center gap-4">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Self-Check') }}</button>
                <button type="button" onclick="aiInsights()" class="text-sm text-indigo-600 hover:underline">{{ __('AI Insights') }}</button>
                @if ($current)
                    <span class="text-sm text-slate-500">{{ __('Focus score') }}: <strong>{{ $current->focus_score }}%</strong></span>
                @endif
            </div>
        </form>
        <div id="insights" class="hidden rounded-lg bg-slate-50 p-4 text-sm text-slate-700"></div>
    </div>

    @if ($history->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('History') }}</h2>
            <ul class="divide-y divide-slate-100">
                @foreach ($history as $check)
                    <li class="py-2 flex justify-between text-sm">
                        <span>{{ $check->year }}-W{{ str_pad($check->week, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="font-medium">{{ $check->focus_score }}%</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<script>
async function aiInsights() {
    const box = document.getElementById('insights');
    box.classList.remove('hidden');
    box.textContent = '{{ __('Thinking...') }}';
    try {
        const res = await fetch('{{ route('focusmatrix.self-check.ai-insights') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.ok) {
            box.innerHTML = `<ul>${data.insights.insights.map(i => `<li>${i}</li>`).join('')}</ul><p><strong>{{ __('Next week focus') }}:</strong> ${data.insights.next_week_focus}</p>`;
        } else {
            box.textContent = data.message || '{{ __('AI not available') }}';
        }
    } catch (e) {
        box.textContent = '{{ __('AI not available') }}';
    }
}
</script>
@endsection
