@extends('layouts.shell')

@section('title', __('Edit Assignment'))
@section('page-title', __('Edit Assignment'))

@section('content')
    <div class="space-y-6 max-w-2xl">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Edit Assignment') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('loopengine.team.assignments.update', $assignment) }}" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Process') }}</label>
                    <select name="process_id" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($processes as $process)
                            <option value="{{ $process->id }}" {{ old('process_id', $assignment->process_id) == $process->id ? 'selected' : '' }}>{{ $process->localizedName() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('User') }}</label>
                    <select name="user_id" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $assignment->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $key => $label)
                            <option value="{{ $key }}" {{ old('status', $assignment->status) === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $assignment->notes) }}</textarea>
                </div>

                <div class="flex gap-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update') }}</button>
                    <a href="{{ route('loopengine.team.assignments') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
