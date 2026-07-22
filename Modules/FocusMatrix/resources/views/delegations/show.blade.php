@extends('layouts.shell', ['title' => __('Delegation')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ $delegation->task?->title }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-slate-500">{{ __('Goal') }}</span><p class="font-medium text-slate-900">{{ $delegation->goal }}</p></div>
            <div><span class="text-slate-500">{{ __('Decision scope') }}</span><p class="font-medium text-slate-900">{{ $delegation->decision_scope }}</p></div>
            <div><span class="text-slate-500">{{ __('Delegate') }}</span><p class="font-medium text-slate-900">{{ $delegation->delegateUser?->name ?? $delegation->delegate_name_fallback }}</p></div>
            <div><span class="text-slate-500">{{ __('Deadline') }}</span><p class="font-medium text-slate-900">{{ $delegation->deadline?->format('Y-m-d H:i') ?? '—' }}</p></div>
            <div><span class="text-slate-500">{{ __('Status') }}</span><p class="font-medium text-slate-900">{{ $delegation->status }}</p></div>
            <div><span class="text-slate-500">{{ __('Health score') }}</span><p class="font-medium text-slate-900">{{ $delegation->health_score }}%</p></div>
        </div>

        @if ($delegation->delegate_user_id === auth()->id())
            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <form method="POST" action="{{ route('focusmatrix.delegations.accept', $delegation) }}">
                    @csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Accept') }}</button>
                </form>
                <form method="POST" action="{{ route('focusmatrix.delegations.decline', $delegation) }}">
                    @csrf
                    <div>
                        <input type="text" name="reason" placeholder="{{ __('Reason (optional)') }}" class="rounded-lg border-slate-300 shadow-sm text-sm">
                        <button class="ml-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Decline') }}</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($delegation->delegator_id === auth()->id())
            <form method="POST" action="{{ route('focusmatrix.delegations.update', $delegation) }}" class="pt-4 border-t border-slate-100 space-y-3">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Update status') }}</label>
                    <select name="status" class="mt-1 rounded-lg border-slate-300 shadow-sm">
                        @foreach (['open','invited','accepted','declined','in_progress','done','overdue','cancelled'] as $s)
                            <option value="{{ $s }}" {{ $delegation->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Health score') }}</label>
                    <input type="number" name="health_score" value="{{ $delegation->health_score }}" min="0" max="100" class="mt-1 rounded-lg border-slate-300 shadow-sm">
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update') }}</button>
            </form>
        @endif
    </div>
</div>
@endsection
