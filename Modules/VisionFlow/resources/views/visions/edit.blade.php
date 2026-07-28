@extends('layouts.shell', ['title' => __('Edit Vision')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('visionflow.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('visionflow.organizations.visions.index', $organization) }}" class="hover:text-indigo-600">{{ __('Visions') }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ __('Edit Vision') }}</span>
    </div>

    <h1 class="text-3xl font-bold text-slate-900">{{ __('Edit Vision') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('visionflow.organizations.visions.update', [$organization, $item]) }}" class="space-y-5">
            @csrf @method('PATCH')
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Content") }}</label>
    <textarea name="content" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ $item->content }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Status") }}</label>
    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="drafting" {{ $item->status == 'drafting' ? 'selected' : '' }}>{{ __("drafting") }}</option>
        <option value="reviewing" {{ $item->status == 'reviewing' ? 'selected' : '' }}>{{ __("reviewing") }}</option>
        <option value="approved" {{ $item->status == 'approved' ? 'selected' : '' }}>{{ __("approved") }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Version") }}</label>
    <input type="number" name="version" value="{{ $item->version }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('visionflow.organizations.visions.index', $organization) }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Update Vision') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
