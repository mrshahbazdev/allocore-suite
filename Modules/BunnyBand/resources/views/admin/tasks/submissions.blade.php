@extends('layouts.shell')

@section('title', __('Submissions'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Task Submissions') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="pb-2 pr-4">{{ __('User') }}</th><th class="pb-2 pr-4">{{ __('Task') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($submissions as $submission)
                        <tr>
                            <td class="py-2 pr-4">{{ $submission->profile?->user?->name }}</td>
                            <td class="py-2 pr-4">{{ $submission->task->title }}</td>
                            <td class="py-2 pr-4">{{ $submission->status }}</td>
                            <td class="py-2 pr-4">{{ $submission->created_at->format('Y-m-d') }}</td>
                            <td class="py-2">
                                <form method="POST" action="{{ route('bunnyband.admin.tasks.submissions.verify', $submission) }}" class="inline">@csrf<button name="action" value="approve" class="text-emerald-600">{{ __('Approve') }}</button> <button name="action" value="reject" class="text-rose-600">{{ __('Reject') }}</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $submissions->links() }}</div>
        </div>
    </div>
@endsection
