@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('AuditPro Challenge') }}</p>
        <h1 class="text-2xl font-bold text-slate-900">{{ $challenge->pillar }}</h1>
        <p class="text-sm text-slate-500">{{ __('A 4-week cybernetic control loop: Plan → Do → Check → Act.') }}</p>
    </div>

    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">{{ __('Status') }}</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">{{ __(ucfirst($challenge->status)) }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-slate-500">{{ __('Progress') }}</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">{{ $challenge->completionPercentage() }}%</p>
            </div>
        </div>
        <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-indigo-600" style="width: {{ $challenge->completionPercentage() }}%"></div>
        </div>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900">{{ __('Steps') }}</h2>
        <ul class="mt-4 space-y-4">
            @foreach ($challenge->steps as $step)
                <li class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start gap-4">
                        <form method="POST" action="{{ route('audit.challenges.toggle-step', $challenge) }}" class="mt-0.5">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="step_id" value="{{ $step['id'] }}">
                            <input type="hidden" name="completed" value="{{ $step['completed'] ? '0' : '1' }}">
                            <button type="submit" class="flex h-6 w-6 items-center justify-center rounded-full border-2 {{ $step['completed'] ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 bg-white' }}">
                                @if ($step['completed'])
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </button>
                        </form>
                        <div class="flex-1">
                            <p class="text-sm font-semibold {{ $step['completed'] ? 'text-slate-500 line-through' : 'text-slate-900' }}">{!! $step['label_html'] ?? e($step['label']) !!}</p>

                            @if (! empty($step['glossary_terms']) && $step['glossary_terms']->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($step['glossary_terms'] as $term)
                                        <a href="{{ route('glossary.show', $term->slug) }}" target="_blank" class="rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">{{ $term->term }}</a>
                                    @endforeach
                                </div>
                            @endif

                            @if ($step['module_name'])
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs text-slate-500">{{ __('Tool:') }} {{ $step['module_name'] }}</span>
                                    @if ($step['subscribed'] && $step['module_route'])
                                        <a href="{{ $step['module_route'] }}" class="text-xs font-medium text-indigo-600 hover:underline">{{ __('Open') }}</a>
                                    @elseif ($step['module_key'])
                                        <a href="{{ route('billing.plans', ['module' => $step['module_key']]) }}" class="text-xs font-medium text-emerald-600 hover:underline">{{ __('Add') }}</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    @if ($challenge->status === 'completed' && $challenge->next_challenge_at)
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-600">{{ __('Next challenge or small audit possible after') }} <span class="font-semibold">{{ $challenge->next_challenge_at->format('d.m.Y') }}</span>.</p>
        </div>
    @endif
@endsection
