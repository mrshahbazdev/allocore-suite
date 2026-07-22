@extends('layouts.shell')

@section('title', __('Process Templates'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Process Templates') }}</h1>
            <a href="{{ route('dentaltrack.admin.process-templates.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Step') }}</a>
        </div>

        <form method="GET" class="flex gap-3">
            <select name="product_type_id" class="rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                <option value="">{{ __('All product types') }}</option>
                @foreach ($productTypes as $pt)
                    <option value="{{ $pt->id }}" {{ request('product_type_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                @endforeach
            </select>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Sort') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Step') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Product Type') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Expected Minutes') }}</th><th class="px-4 py-2 text-right text-xs font-medium text-slate-500">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($templates as $template)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $template->sort_order }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $template->step_name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $template->productType?->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $template->expected_minutes ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('dentaltrack.admin.process-templates.edit', $template) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('dentaltrack.admin.process-templates.destroy', $template) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-rose-600 hover:underline">{{ __('Delete') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-4 text-sm text-slate-500">{{ __('No template steps found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $templates->links() }}</div>
    </div>
@endsection
