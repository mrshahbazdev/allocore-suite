@extends('layouts.shell')

@section('title', __('Reminders'))
@section('page-title', __('Reminders'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Reminders') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Title') }}</th><th class="pb-2 pr-4">{{ __('Remind At') }}</th><th class="pb-2 pr-4">{{ __('Done') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($reminders as $reminder)
                        <tr>
                            <td class="py-2 pr-4">{{ $reminder->title }}</td>
                            <td class="py-2 pr-4">{{ $reminder->remind_at->format('Y-m-d H:i') }}</td>
                            <td class="py-2 pr-4">{{ $reminder->is_done ? __('Yes') : __('No') }}</td>
                            <td class="py-2"><a href="{{ route('planhive.reminders.edit', $reminder) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $reminders->links() }}</div>
        </div>
    </div>
@endsection
