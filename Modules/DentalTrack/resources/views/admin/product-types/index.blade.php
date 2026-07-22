@extends('layouts.shell')

@section('title', __('Product Types'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Product Types') }}</h1>
            <a href="{{ route('dentaltrack.admin.product-types.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Product Type') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Name') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Company') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Description') }}</th><th class="px-4 py-2 text-right text-xs font-medium text-slate-500">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productTypes as $type)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $type->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $type->company?->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500 truncate max-w-xs">{{ $type->description }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('dentaltrack.admin.product-types.edit', $type) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('dentaltrack.admin.product-types.destroy', $type) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-rose-600 hover:underline">{{ __('Delete') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-4 text-sm text-slate-500">{{ __('No product types found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $productTypes->links() }}</div>
    </div>
@endsection
