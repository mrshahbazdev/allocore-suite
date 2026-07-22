@extends('layouts.shell')

@section('title', __('Workstations'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Workstations') }}</h1>
            <a href="{{ route('dentaltrack.admin.workstations.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Workstation') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Name') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Lab') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Type') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('QR Code') }}</th><th class="px-4 py-2 text-right text-xs font-medium text-slate-500">{{ __('Actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($workstations as $ws)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $ws->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $ws->lab?->name }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ str_replace('_', ' ', $ws->type->value) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-xs">{{ $ws->qr_code }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('dentaltrack.admin.workstations.sticker', $ws) }}" class="text-slate-600 hover:underline">{{ __('Sticker') }}</a>
                                <a href="{{ route('dentaltrack.admin.workstations.edit', $ws) }}" class="text-indigo-600 hover:underline ml-2">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('dentaltrack.admin.workstations.destroy', $ws) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-rose-600 hover:underline">{{ __('Delete') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-4 text-sm text-slate-500">{{ __('No workstations found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $workstations->links() }}</div>
    </div>
@endsection
