@extends('layouts.shell', ['title' => $task->title])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ $task->title }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <form method="POST" action="{{ route('focusmatrix.tasks.update', $task) }}" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ $task->title }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $task->description }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                        @foreach (['inbox','keep','delegate','drop','done'] as $s)
                            <option value="{{ $s }}" {{ $task->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Only-You Category') }}</label>
                    <select name="only_you_category" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                        <option value="">—</option>
                        @foreach (Modules\FocusMatrix\Models\Task::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" {{ $task->only_you_category === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Due at') }}</label>
                    <input type="datetime-local" name="due_at" value="{{ $task->due_at?->format('Y-m-d\\TH:i') }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Focused block at') }}</label>
                    <input type="datetime-local" name="focused_block_at" value="{{ $task->focused_block_at?->format('Y-m-d\\TH:i') }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('focusmatrix.tasks.triage', $task) }}" class="rounded-lg bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-200">{{ __('Triage') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>

        @if ($task->delegation)
            <div class="border-t border-slate-100 pt-4">
                <h3 class="font-semibold text-slate-900">{{ __('Delegation') }}</h3>
                <a href="{{ route('focusmatrix.delegations.show', $task->delegation) }}" class="text-indigo-600 hover:underline">{{ __('View delegation') }}</a>
            </div>
        @elseif ($task->status === 'delegate')
            <div class="border-t border-slate-100 pt-4">
                <a href="{{ route('focusmatrix.delegations.create', ['task' => $task->id]) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create delegation') }}</a>
            </div>
        @endif
    </div>
</div>
@endsection
