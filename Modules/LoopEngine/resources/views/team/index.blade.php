@extends('layouts.shell')

@section('title', __('Team'))
@section('page-title', __('Team'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Team') }}</h1>
            <a href="{{ route('loopengine.team.assign.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Assign Process') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Runs') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($members as $member)
                        <tr>
                            <td class="py-2 pr-4">{{ $member->name }}</td>
                            <td class="py-2 pr-4">{{ $runsByMember[$member->id] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $members->links() }}</div>
        </div>
    </div>
@endsection
