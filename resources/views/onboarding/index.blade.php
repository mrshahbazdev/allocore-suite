@extends('layouts.shell', ['title' => __('Welcome')])

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('Welcome to :app', ['app' => config('app.name')]) }}</h1>
        <p class="mt-2 text-slate-600">{{ __('Complete these steps to get started.') }}</p>
    </div>

    <div class="mb-8 flex items-center justify-between gap-2">
        @foreach ([__('Team'), __('Plan'), __('Explore')] as $i => $label)
            <div class="flex flex-1 items-center">
                <div class="h-8 w-8 rounded-full text-sm font-bold flex items-center justify-center {{ $step >= $i ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500' }}">{{ $i + 1 }}</div>
                <div class="ml-2 hidden text-sm font-medium sm:block {{ $step >= $i ? 'text-slate-900' : 'text-slate-400' }}">{{ $label }}</div>
                @if ($i < 2)
                    <div class="mx-2 h-1 flex-1 rounded {{ $step > $i ? 'bg-indigo-600' : 'bg-slate-200' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    @if ($step === 0)
        <div class="space-y-6">
            @if ($teams->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-xl font-semibold text-slate-900">{{ __('Your companies') }}</h2>
                    <ul class="divide-y divide-slate-100">
                        @foreach ($teams as $team)
                            <li class="flex items-center justify-between py-3">
                                <div>
                                    <div class="font-medium text-slate-900">{{ $team->name }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $team->industry }}{{ $team->industry && $team->country ? ' · ' : '' }}{{ $team->country }}{{ $team->size ? ' · ' . $team->size : '' }}
                                    </div>
                                </div>
                                @if ($team->id === auth()->user()->current_team_id)
                                    <span class="rounded-full bg-[#ff9200]/10 px-2 py-0.5 text-xs font-medium text-[#ff9200]">{{ __('Current') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-semibold text-slate-900">{{ $teams->isEmpty() ? __('Create your first company') : __('Add another company') }}</h2>
                <form method="POST" action="{{ route('onboarding.team') }}" class="space-y-4">
                    @csrf
                    @include('partials.team-form-fields', ['clusters' => $industryClusters])
                    <button type="submit" class="rounded-lg bg-[#ff9200] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#e58200]">{{ $teams->isEmpty() ? __('Create company') : __('Add company') }}</button>
                </form>
            </div>

            @if ($teams->isNotEmpty())
                <form method="POST" action="{{ route('onboarding.continue') }}" class="flex justify-end">
                    @csrf
                    <button type="submit" class="rounded-lg bg-[#0094af] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#007a8f]">{{ __('Continue to plan') }} →</button>
                </form>
            @endif
        </div>
    @elseif ($step === 1)
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xl font-semibold text-slate-900">{{ __('Pick a starter plan') }}</h2>
            <p class="mb-4 text-sm text-slate-500">{{ __('Start with a 14-day free trial of any plan.') }}</p>
            <form method="POST" action="{{ route('onboarding.plan') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($plans as $plan)
                        <label class="rounded-lg border border-slate-200 p-4 hover:border-indigo-300 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-600" {{ $loop->first ? 'checked' : '' }}>
                            <div class="mt-2 font-semibold text-slate-900">{{ $plan->name }}</div>
                            <div class="text-sm text-slate-500">{{ $plan->description }}</div>
                            <div class="mt-1 text-sm font-medium text-indigo-600">{{ number_format($plan->price_monthly, 2) }} {{ $plan->currency }}</div>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Start trial') }}</button>
            </form>
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xl font-semibold text-slate-900">{{ __('Explore your tools') }}</h2>
            <p class="mb-4 text-sm text-slate-500">{{ __('Your subscribed tools are ready. Open one to create your first record.') }}</p>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($modules as $module)
                    @if ($user->hasModule($module->key))
                        <a href="{{ route('tools.index') }}" class="block rounded-lg border border-slate-200 p-4 hover:border-indigo-300 hover:bg-slate-50">
                            <div class="font-semibold text-slate-900">{{ $module->name }}</div>
                            <div class="text-sm text-slate-500">{{ $module->description }}</div>
                        </a>
                    @endif
                @endforeach
            </div>

            <form method="POST" action="{{ route('onboarding.complete') }}" class="mt-6">
                @csrf
                <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('Finish onboarding') }}</button>
            </form>
        </div>
    @endif
</div>
@endsection
