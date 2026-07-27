@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('AuditPro') }}</p>
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Challenges') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Cybernetic control loops based on your small audits.') }}</p>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">{{ __('Your challenges') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">{{ __('Pillar') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3">{{ __('Progress') }}</th>
                        <th class="px-5 py-3">{{ __('Started') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($challenges as $challenge)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-900">{{ $challenge->pillar }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-medium
                                    {{ $challenge->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($challenge->status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ __(ucfirst($challenge->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-indigo-600" style="width: {{ $challenge->completionPercentage() }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-600">{{ $challenge->completionPercentage() }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $challenge->started_at?->format('d.m.Y') ?? '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('audit.challenges.show', $challenge) }}" class="font-medium text-indigo-600 hover:underline">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">{{ __('No challenges yet. Start one from a small audit result.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($challenges->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $challenges->links() }}</div>
        @endif
    </section>
@endsection
