@extends('layouts.shell')

@section('title', __('Assignments'))
@section('page-title', __('Assignments'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Assignments') }}</h1>
            <a href="{{ route('loopengine.team.assign.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Assignment') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Process') }}</th><th class="pb-2 pr-4">{{ __('User') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2 pr-4">{{ __('Assigned') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($assignments as $assignment)
                        <tr>
                            <td class="py-2 pr-4">{{ $assignment->process->localizedName() }}</td>
                            <td class="py-2 pr-4">{{ $assignment->user->name }}</td>
                            <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $assignment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($assignment->status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700') }}">{{ __($assignment->status) }}</span></td>
                            <td class="py-2 pr-4">{{ $assignment->assigned_at?->format('Y-m-d H:i') }}</td>
                            <td class="py-2 flex gap-2">
                                <a href="{{ route('loopengine.team.assignments.edit', $assignment) }}" class="text-indigo-600">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('loopengine.team.assignments.destroy', $assignment) }}" class="inline" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-rose-600">{{ __('Delete') }}</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $assignments->links() }}</div>
        </div>
    </div>
@endsection
