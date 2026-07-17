@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Status Incidents') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Manage public status page incidents.') }}</p>
        </div>
        <a href="{{ route('admin.status-incidents.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('New incident') }}</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Title') }}</th>
                    <th class="px-4 py-3">{{ __('Severity') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Started') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($incidents as $incident)
                    <tr>
                        <td class="px-4 py-3 text-slate-900">{{ $incident->title }}</td>
                        <td class="px-4 py-3">
                            @php($badge = match($incident->severity) { 'critical' => 'bg-rose-100 text-rose-700', 'major' => 'bg-orange-100 text-orange-700', 'minor' => 'bg-amber-100 text-amber-700', default => 'bg-slate-100 text-slate-700' })
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">{{ $incident->severity }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $incident->status }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $incident->started_at->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                @if (! $incident->is_resolved)
                                    <form method="POST" action="{{ route('admin.status-incidents.resolve', $incident) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-sm font-medium text-emerald-600 hover:underline">{{ __('Resolve') }}</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.status-incidents.edit', $incident) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.status-incidents.destroy', $incident) }}" onsubmit="return confirm('{{ __('Delete this incident?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('No incidents yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $incidents->links() }}</div>
@endsection
