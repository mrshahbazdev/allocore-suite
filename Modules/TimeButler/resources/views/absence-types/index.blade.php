@extends('layouts.shell')

@section('title', __('Absence Types'))
@section('page-title', __('Absence Types'))

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Absence Types') }}</h1>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">{{ __('Name') }}</th>
                            <th class="pb-2 pr-4">{{ __('Approval') }}</th>
                            <th class="pb-2 pr-4">{{ __('Paid') }}</th>
                            <th class="pb-2 pr-4">{{ __('Deducts Vacation') }}</th>
                            <th class="pb-2 pr-4">{{ __('Active') }}</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($types as $type)
                            <tr>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-2 py-0.5 text-xs font-medium" style="background-color: {{ $type->color }}20; color: {{ $type->color }}">
                                        {{ $type->name }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4">{{ $type->requires_approval ? __('Yes') : __('No') }}</td>
                                <td class="py-2 pr-4">{{ $type->is_paid ? __('Yes') : __('No') }}</td>
                                <td class="py-2 pr-4">{{ $type->deducts_vacation ? __('Yes') : __('No') }}</td>
                                <td class="py-2 pr-4">{{ $type->is_active ? __('Yes') : __('No') }}</td>
                                <td class="py-2">
                                    <form method="POST" action="{{ route('timebutler.absence-types.destroy', $type) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-600">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Type') }}</h2>
            <form method="POST" action="{{ route('timebutler.absence-types.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                    <input type="text" name="name" class="mt-1 w-full rounded-lg border-slate-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Color') }}</label>
                    <input type="color" name="color" value="#3b82f6" class="mt-1 h-10 w-full rounded-lg border-slate-300">
                </div>
                <div class="space-y-2 text-sm">
                    <label class="flex items-center gap-2"><input type="checkbox" name="requires_approval" value="1" checked class="rounded border-slate-300"> {{ __('Requires approval') }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_paid" value="1" checked class="rounded border-slate-300"> {{ __('Paid') }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="deducts_vacation" value="1" class="rounded border-slate-300"> {{ __('Deducts vacation') }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300"> {{ __('Active') }}</label>
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </form>
        </div>
    </div>
@endsection
