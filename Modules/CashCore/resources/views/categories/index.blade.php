@extends('layouts.shell')

@section('title', __('Categories'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Categories') }}</h1>
            <a href="{{ route('cashcore.categories.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Category') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Color') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($categories as $category)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $category->icon }} {{ $category->name }}</td>
                            <td class="py-2 pr-4">{{ __($category->type) }}</td>
                            <td class="py-2 pr-4"><span class="inline-block h-4 w-4 rounded-full" style="background-color: {{ $category->color }}"></span></td>
                            <td class="py-2 flex gap-2">
                                <a href="{{ route('cashcore.categories.edit', $category) }}" class="text-indigo-600">{{ __('Edit') }}</a>
                                @if (! $category->is_default)
                                    <form method="POST" action="{{ route('cashcore.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-rose-600">{{ __('Delete') }}</button></form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
