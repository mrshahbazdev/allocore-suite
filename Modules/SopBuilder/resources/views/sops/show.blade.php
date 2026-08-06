@php $title = $sop->title; @endphp
@extends('layouts.shell')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $sop->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($sop->status === 'archived' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700') }}">{{ $sop->status }}</span>
                <span>{{ __('Version') }} {{ $sop->version }}</span>
                @if($sop->category)<span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $sop->category->name }}</span>@endif
            </div>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $sop->title }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($sop->status !== 'published')
                <form method="POST" action="{{ route('sopbuilder.sops.publish', $sop) }}">@csrf
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Publish') }}</button>
                </form>
            @endif
            <form method="POST" action="{{ route('sopbuilder.sops.duplicate', $sop) }}">@csrf
                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Duplicate') }}</button>
            </form>
            <a href="{{ route('sopbuilder.sops.edit', $sop) }}" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">{{ __('Edit') }}</a>
            @if($sop->status === 'published')
                <a href="{{ route('sopbuilder.execute.show', $sop) }}" class="rounded-lg bg-[#0094af] px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-600">{{ __('Execute') }}</a>
            @endif
        </div>
    </div>

    @if($sop->description)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Description') }}</h2>
            <p class="mt-2 whitespace-pre-line text-slate-700">{{ $sop->description }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if($sop->why)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-semibold text-slate-900">{{ __('Why') }}</h3><p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $sop->why }}</p></div>@endif
        @if($sop->who)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-semibold text-slate-900">{{ __('Who') }}</h3><p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $sop->who }}</p></div>@endif
        @if($sop->when)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-semibold text-slate-900">{{ __('When') }}</h3><p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $sop->when }}</p></div>@endif
        @if($sop->input)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-semibold text-slate-900">{{ __('Input') }}</h3><p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $sop->input }}</p></div>@endif
        @if($sop->output)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-semibold text-slate-900">{{ __('Output') }}</h3><p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $sop->output }}</p></div>@endif
        @if($sop->risks)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-semibold text-slate-900">{{ __('Risks') }}</h3><p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $sop->risks }}</p></div>@endif
        @if($sop->tools)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:col-span-2"><h3 class="font-semibold text-slate-900">{{ __('Tools') }}</h3><p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $sop->tools }}</p></div>@endif
    </div>

    @if($sop->steps->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Steps') }}</h2>
            <ol class="mt-4 list-decimal list-inside space-y-3">
                @foreach($sop->steps as $step)
                    <li class="rounded-xl border border-slate-100 p-3">
                        <span class="font-medium text-slate-900">{{ $step->title }}</span>
                        @if($step->description)<p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $step->description }}</p>@endif
                        @if($step->checklistItems->isNotEmpty())
                            <ul class="mt-2 ml-4 list-disc text-sm text-slate-600">
                                @foreach($step->checklistItems as $item)
                                    <li>{{ $item->text }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    @if($sop->checklistItems->whereNull('step_id')->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Checklist') }}</h2>
            <ul class="mt-4 space-y-2">
                @foreach($sop->checklistItems->whereNull('step_id') as $item)
                    <li class="flex items-start gap-2 text-slate-700">
                        <span class="mt-1 h-4 w-4 shrink-0 rounded border border-slate-300"></span>
                        <span>{{ $item->text }} @if($item->is_required)<span class="text-xs text-rose-600">({{ __('required') }})</span>@endif</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($sop->quizzes->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Quiz') }}</h2>
            <div class="mt-4 space-y-4">
                @foreach($sop->quizzes as $quiz)
                    <div class="rounded-xl border border-slate-100 p-4">
                        <p class="font-medium text-slate-900">{{ $loop->iteration }}. {{ $quiz->question }}</p>
                        @if(is_array($quiz->options) && count($quiz->options))
                            <ul class="mt-2 ml-4 list-disc text-sm text-slate-600">
                                @foreach($quiz->options as $option)
                                    <li>{{ $option }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($sop->completions->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Completions') }}</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach($sop->completions as $completion)
                    <li class="flex items-center justify-between rounded-lg border border-slate-100 p-3">
                        <span>{{ $completion->user?->name ?? '-' }} — {{ $completion->completed_at?->format('M d, Y H:i') }}</span>
                        @if($completion->score !== null)
                            <span class="font-semibold {{ $completion->score >= 80 ? 'text-emerald-600' : ($completion->score >= 50 ? 'text-amber-600' : 'text-rose-600') }}">{{ $completion->score }}%</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($sop->evidence->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Evidence') }}</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach($sop->evidence as $evidence)
                    <li class="rounded-lg border border-slate-100 p-3">
                        <a href="{{ asset('storage/'.$evidence->file_path) }}" target="_blank" class="font-medium text-[#0094af] hover:underline">{{ basename($evidence->file_path) }}</a>
                        <div class="text-xs text-slate-500">{{ $evidence->user?->name }} — {{ $evidence->created_at?->format('M d, Y') }}</div>
                        @if($evidence->notes)<p class="text-slate-600">{{ $evidence->notes }}</p>@endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
