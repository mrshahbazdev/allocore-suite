@extends('layouts.shell')

@section('title', __('Goals'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Goals') }}</h1>
            <a href="{{ route('smartkpi.goals.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Goal') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Target') }}</th><th class="pb-2 pr-4">{{ __('Current') }}</th><th class="pb-2 pr-4">{{ __('Progress') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($goals as $goal)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $goal->localizedName() }}</td>
                            <td class="py-2 pr-4">{{ $goal->target_value }}</td>
                            <td class="py-2 pr-4">{{ $goal->current_value }}</td>
                            <td class="py-2 pr-4">{{ number_format($goal->progress, 0) }}%</td>
                            <td class="py-2 pr-4">{{ $goal->status }}</td>
                            <td class="py-2"><a href="{{ route('smartkpi.goals.edit', $goal) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $goals->links() }}</div>
        </div>
    </div>
@endsection
