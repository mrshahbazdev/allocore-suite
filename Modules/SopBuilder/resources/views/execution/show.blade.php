@php $title = __('Execute SOP'); @endphp
@extends('layouts.shell')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $sop->title }}</h1>
            <p class="text-sm text-slate-500">{{ __('Follow the SOP, complete the checklist and answer the quiz.') }}</p>
        </div>
        <a href="{{ route('sopbuilder.sops.show', $sop) }}" class="text-sm text-slate-600 hover:text-[#ff9200]">{{ __('Back') }}</a>
    </div>

    <form method="POST" action="{{ route('sopbuilder.execute.store', $sop) }}" class="space-y-6">
        @csrf

        @if($sop->steps->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Steps') }}</h2>
                <ol class="mt-4 list-decimal list-inside space-y-3">
                    @foreach($sop->steps as $step)
                        <li class="rounded-xl border border-slate-100 p-3">
                            <span class="font-medium text-slate-900">{{ $step->title }}</span>
                            @if($step->description)<p class="mt-1 text-sm text-slate-600 whitespace-pre-line">{{ $step->description }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        @if($sop->checklistItems->whereNull('step_id')->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Checklist') }}</h2>
                <div class="mt-4 space-y-2">
                    @foreach($sop->checklistItems->whereNull('step_id') as $item)
                        <label class="flex items-start gap-3 rounded-xl border border-slate-100 p-3 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="checklist[{{ $item->id }}]" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                            <div>
                                <span class="text-slate-700">{{ $item->text }}</span>
                                @if($item->is_required)<span class="ml-2 text-xs text-rose-600">({{ __('required') }})</span>@endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if($sop->quizzes->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Knowledge Check') }}</h2>
                <div class="mt-4 space-y-6">
                    @foreach($sop->quizzes as $quiz)
                        <div class="rounded-xl border border-slate-100 p-4">
                            <p class="font-medium text-slate-900">{{ $loop->iteration }}. {{ $quiz->question }}</p>
                            @if($quiz->answer_type === 'text')
                                <textarea name="answers[{{ $quiz->id }}]" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
                            @elseif($quiz->answer_type === 'multiple')
                                <div class="mt-2 space-y-2">
                                    @foreach($quiz->options ?? [] as $option)
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input type="checkbox" name="answers[{{ $quiz->id }}][]" value="{{ $option }}" class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                                            {{ $option }}
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-2 space-y-2">
                                    @foreach($quiz->options ?? [] as $option)
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input type="radio" name="answers[{{ $quiz->id }}]" value="{{ $option }}" class="border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                                            {{ $option }}
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <label class="block text-sm font-medium text-slate-700">{{ __('Notes / Evidence summary') }}</label>
            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sopbuilder.evidence.create', $sop) }}" class="rounded-lg border border-slate-300 bg-white px-6 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Upload Evidence') }}</a>
            <button type="submit" class="rounded-lg bg-[#ff9200] px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">{{ __('Mark as Completed') }}</button>
        </div>
    </form>
</div>
@endsection
