@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.backups.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.backups.description') }}</p>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.backups.export.users') }}" class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm hover:bg-slate-50">
            <div class="text-sm font-semibold text-slate-900">{{ __('admin.backups.export_users') }}</div>
            <div class="text-xs text-slate-500">CSV</div>
        </a>
        <a href="{{ route('admin.backups.export.teams') }}" class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm hover:bg-slate-50">
            <div class="text-sm font-semibold text-slate-900">{{ __('admin.backups.export_teams') }}</div>
            <div class="text-xs text-slate-500">CSV</div>
        </a>
        <a href="{{ route('admin.backups.export.invoices') }}" class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm hover:bg-slate-50">
            <div class="text-sm font-semibold text-slate-900">{{ __('admin.backups.export_invoices') }}</div>
            <div class="text-xs text-slate-500">CSV</div>
        </a>
        <a href="{{ route('admin.backups.export.payments') }}" class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm hover:bg-slate-50">
            <div class="text-sm font-semibold text-slate-900">{{ __('admin.backups.export_payments') }}</div>
            <div class="text-xs text-slate-500">CSV</div>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.backups.store') }}" class="mb-6">
        @csrf
        <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.backups.create_sql_dump') }}</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Size') }}</th>
                    <th class="px-4 py-3">{{ __('Completed') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($backups as $backup)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $backup->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $backup->type }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format($backup->size / 1024, 1) }} KB</td>
                        <td class="px-4 py-3 text-slate-600">{{ $backup->completed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.backups.download', $backup) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Download') }}</a>
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" onsubmit="return confirm('{{ __('admin.backups.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('admin.backups.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $backups->links() }}</div>
@endsection
