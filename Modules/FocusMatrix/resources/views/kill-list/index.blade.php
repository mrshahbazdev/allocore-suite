@extends('layouts.shell', ['title' => __('Drop / Kill List')])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Drop / Kill List') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('What will you drop?') }}</h2>
        <form method="POST" action="{{ route('focusmatrix.kill-list.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Item type') }}</label>
                <select name="item_type" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                    @foreach (['task','meeting','report','process','other'] as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                <input type="text" name="title" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">{{ __('Reason') }}</label>
                <textarea name="reason" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm"></textarea>
            </div>
            <div class="md:col-span-2 flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="was_necessary" value="1" class="rounded border-slate-300"> {{ __('Was necessary?') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="served_clear_goal" value="1" class="rounded border-slate-300"> {{ __('Served clear goal?') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="anything_missing" value="1" class="rounded border-slate-300"> {{ __('Anything missing?') }}</label>
            </div>
            <div class="md:col-span-2">
                <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">{{ __('Drop it') }}</button>
            </div>
        </form>
    </div>

    @if ($items->isNotEmpty())
        <div class="space-y-3">
            @foreach ($items as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-slate-900">{{ $item->title }}</div>
                        <div class="text-sm text-slate-500">{{ $item->item_type }} — {{ $item->killed_at?->format('Y-m-d') }}</div>
                    </div>
                    <form method="POST" action="{{ route('focusmatrix.kill-list.destroy', $item) }}">
                        @csrf @method('DELETE')
                        <button class="text-sm text-rose-600 hover:underline">{{ __('Delete') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
