@extends('layouts.shell')

@section('title', __('Assign Process'))
@section('page-title', __('Assign Process'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Assign Process') }}</h1>
        <form method="POST" action="{{ route('loopengine.team.assign.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Process') }}</label>
                <select name="process_id" class="mt-1 w-full rounded-lg border-slate-300">
                    @foreach ($processes as $process)
                        <option value="{{ $process->id }}">{{ $process->localizedName() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('User') }}</label>
                <select name="user_id" class="mt-1 w-full rounded-lg border-slate-300">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label><textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Assign') }}</button>
        </form>
    </div>
@endsection
