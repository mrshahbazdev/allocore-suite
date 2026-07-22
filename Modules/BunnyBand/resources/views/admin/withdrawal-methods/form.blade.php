@extends('layouts.shell')

@section('title', $method->exists ? __('Edit Method') : __('New Method'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $method->exists ? __('Edit Method') : __('New Method') }}</h1>
        <form method="POST" action="{{ $method->exists ? route('bunnyband.admin.withdrawal-methods.update', $method) : route('bunnyband.admin.withdrawal-methods.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($method->exists) @method('PUT') @endif
            <div><input type="text" name="name" value="{{ old('name', $method->name) }}" placeholder="Name" class="w-full rounded-lg border-slate-300" required></div>
            <div><textarea name="fields" placeholder="JSON field names e.g. {&quot;account_number&quot;:&quot;Account Number&quot;}" rows="3" class="w-full rounded-lg border-slate-300">{{ old('fields', json_encode($method->fields)) }}</textarea></div>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $method->is_active) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm">{{ __('Active') }}</span></label>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
