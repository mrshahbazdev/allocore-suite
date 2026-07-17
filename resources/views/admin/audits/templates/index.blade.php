@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.audit_templates.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.audit_templates.description') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.audits.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to audits') }}</a>
            <a href="{{ route('admin.audits.templates.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.audit_templates.create_button') }}</a>
        </div>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.audits.templates.index') }}" class="flex gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.audit_templates.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('admin.audit_templates.pillars') }}</th>
                    <th class="px-4 py-3">{{ __('admin.audit_templates.audits') }}</th>
                    <th class="px-4 py-3">{{ __('admin.audit_templates.default') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($templates as $template)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $template->name }}</div>
                            <div class="text-xs text-slate-500">{{ $template->slug }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $template->team?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $template->pillars_count }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $template->audits_count }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $template->is_default ? __('Yes') : __('No') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.audits.templates.show', $template) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('View') }}</a>
                                <a href="{{ route('admin.audits.templates.edit', $template) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.audits.templates.destroy', $template) }}" onsubmit="return confirm('{{ __('admin.audit_templates.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">{{ __('admin.audit_templates.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $templates->links() }}</div>
@endsection
