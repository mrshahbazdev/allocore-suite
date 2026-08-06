@php $title = __('SOP Categories'); @endphp
@extends('layouts.shell')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('SOP Categories') }}</h1>
        <a href="{{ route('sopbuilder.categories.create') }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">{{ __('New Category') }}</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Name') }}</th><th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Color') }}</th><th class="px-4 py-3 text-right font-semibold text-slate-700">{{ __('Actions') }}</th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $category->name }}</td>
                        <td class="px-4 py-3"><span class="inline-block h-4 w-4 rounded-full" style="background-color: {{ $category->color ?? '#cbd5e1' }}"></span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('sopbuilder.categories.edit', $category) }}" class="text-[#ff9200] hover:underline">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('sopbuilder.categories.destroy', $category) }}" class="inline ml-3" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">{{ __('No categories found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
