@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Workflows') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Automate cross-tool actions when activity events happen.') }}</p>
        </div>
        <a href="{{ route('workflows.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create workflow') }}</a>
    </div>

    @if ($workflows->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No workflows yet.') }}</div>
    @else
        <div class="space-y-3">
            @foreach ($workflows as $workflow)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $workflow->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ __('When') }} <span class="font-medium">{{ $workflow->trigger_event }}</span>
                                @if ($workflow->subject_type)
                                    {{ __('on') }} <span class="font-medium">{{ $workflow->subject_type }}</span>
                                @endif
                                → {{ $workflow->action }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">{{ $workflow->action_payload['message'] ?? '' }}</p>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $workflow->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $workflow->is_active ? __('Active') : __('Inactive') }}</span>
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <a href="{{ route('workflows.edit', $workflow) }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('workflows.destroy', $workflow) }}" onsubmit="return confirm('{{ __('Delete this workflow?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
