@extends('layouts.shell')

@section('title', __('Manage Tasks'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between"><h1 class="text-2xl font-bold text-slate-900">{{ __('Tasks') }}</h1><a href="{{ route('bunnyband.admin.tasks.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('New Task') }}</a></div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Title') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Reward') }}</th><th class="pb-2 pr-4">{{ __('Active') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($tasks as $task)
                        <tr>
                            <td class="py-2 pr-4">{{ $task->title }}</td>
                            <td class="py-2 pr-4">{{ $task->type }}</td>
                            <td class="py-2 pr-4">{{ number_format($task->reward, 2) }}</td>
                            <td class="py-2 pr-4">{{ $task->is_active ? 'Yes' : 'No' }}</td>
                            <td class="py-2"><a href="{{ route('bunnyband.admin.tasks.edit', $task) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $tasks->links() }}</div>
        </div>
    </div>
@endsection
