@extends('layouts.shell')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $project->name }}</h1>
            <p class="text-sm text-slate-500">{{ __('Answer the structured questions to build the knowledge base.') }}</p>
        </div>
        <a href="{{ route('knowledgemanager.projects.show', $project) }}" class="text-sm text-slate-600 hover:text-[#ff9200]">{{ __('Cancel') }}</a>
    </div>

    <form method="POST" action="{{ route('knowledgemanager.answers.update', $project) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach(config('knowledgemanager.sections') as $sectionKey => $section)
            @php($existing = $project->answers->where('section', $sectionKey)->keyBy('question_key'))
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ $section['label'] }}</h2>
                @foreach($section['questions'] as $question)
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ $question['label'] }}</label>
                        <textarea name="answers[{{ $sectionKey }}][{{ $question['key'] }}]" rows="3" placeholder="{{ $question['placeholder'] ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#0094af] focus:ring-[#0094af]">{{ old('answers.'.$sectionKey.'.'.$question['key'], $existing->get($question['key'])?->answer) }}</textarea>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Save Answers') }}</button>
        </div>
    </form>
</div>
@endsection
