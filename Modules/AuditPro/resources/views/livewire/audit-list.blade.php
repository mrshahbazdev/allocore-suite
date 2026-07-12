<div>
    @include('auditpro::partials.nav')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Audit history') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Search, resume, and review the current team’s assessments.') }}</p>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach ([__('Total') => $stats['total'], __('Completed') => $stats['completed'], __('In progress') => $stats['in_progress']] as $label => $value)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Search template or owner') }}" class="w-full rounded-lg border-slate-300 text-sm sm:max-w-sm">
            <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm">
                <option value="">{{ __('All statuses') }}</option>
                <option value="in_progress">{{ __('In progress') }}</option>
                <option value="completed">{{ __('Completed') }}</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">{{ __('Template') }}</th>
                        <th class="px-5 py-3">{{ __('Owner') }}</th>
                        <th class="px-5 py-3">{{ __('Date') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3">{{ __('Score') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($audits as $audit)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-900">{{ $audit->template?->name ?? __('Archived template') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $audit->creator?->name ?? __('Deleted user') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $audit->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $audit->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $audit->status === 'completed' ? __('Completed') : __('In progress') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $audit->status === 'completed' ? number_format((float) $audit->results->avg('average_score'), 1).'/5' : '—' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ $audit->status === 'completed' ? route('audit.results', $audit) : route('audit.assessment', $audit) }}" class="font-medium text-indigo-600 hover:underline">
                                        {{ $audit->status === 'completed' ? __('View') : __('Resume') }}
                                    </a>
                                    <form method="POST" action="{{ route('audit.destroy', $audit) }}" onsubmit="return confirm('{{ __('Delete this audit?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">{{ __('No matching audits.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{ $audits->links() }}</div>
    </div>
</div>
