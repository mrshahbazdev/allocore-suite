@extends('layouts.shell', ['title' => __('Vision')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    <h1 class="text-2xl font-bold text-slate-900">{{ __('Vision Statement') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('nurdu.vision.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Statement') }}</label>
                <textarea name="statement" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>{{ $vision->statement ?? '' }}</textarea>
            </div>
            <div class="flex justify-end">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save Vision') }}</button>
            </div>
        </form>
    </div>

    <h2 class="text-xl font-bold text-slate-900">{{ __('Guiding Principles') }}</h2>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('nurdu.vision.principles.store') }}" class="space-y-4">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                    <input type="text" name="title" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <input type="text" name="description" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="flex justify-end">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Principle') }}</button>
            </div>
        </form>
    </div>

    @if ($vision && $vision->guidingPrinciples->isNotEmpty())
        <div class="space-y-3">
            @foreach ($vision->guidingPrinciples as $principle)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <form method="POST" action="{{ route('nurdu.vision.principles.update', $principle) }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <div class="grid md:grid-cols-2 gap-4">
                            <input type="text" name="title" value="{{ $principle->title }}" class="rounded-lg border-slate-300 shadow-sm" required>
                            <input type="text" name="description" value="{{ $principle->description }}" class="rounded-lg border-slate-300 shadow-sm">
                        </div>
                        <div class="flex justify-end gap-3">
                            <button class="text-indigo-600 hover:underline text-sm">{{ __('Update') }}</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('nurdu.vision.principles.destroy', $principle) }}" class="inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                        @csrf @method('DELETE')
                        <button class="text-rose-600 hover:underline text-sm">{{ __('Delete') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
