@php $title = $sop ? __('Edit SOP') : __('New SOP'); @endphp
@extends('layouts.shell')

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
         steps: {{ json_encode($steps->map(fn($s)=>['id'=>$s->id,'title'=>$s->title,'description'=>$s->description])->values()) }},
         checklist: {{ json_encode($checklist->map(fn($i)=>['id'=>$i->id,'text'=>$i->text,'is_required'=>$i->is_required])->values()) }},
         quizzes: {{ json_encode($quizzes->map(fn($q)=>['id'=>$q->id,'question'=>$q->question,'answer_type'=>$q->answer_type,'options'=>is_array($q->options)?implode(PHP_EOL,$q->options):$q->options,'correct_answer'=>$q->correct_answer,'explanation'=>$q->explanation])->values()) }},
     }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
        <a href="{{ route('sopbuilder.sops.index') }}" class="text-sm text-slate-600 hover:text-[#ff9200]">{{ __('Cancel') }}</a>
    </div>

    <form method="POST" action="{{ $sop ? route('sopbuilder.sops.update', $sop) : route('sopbuilder.sops.store') }}" class="space-y-6">
        @csrf
        @if($sop) @method('PUT') @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('SOP Details') }}</h2>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title', $sop?->title) }}" required class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
                    <select name="category_id" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('None') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $sop?->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        @foreach(['draft','published','archived'] as $status)
                            <option value="{{ $status }}" {{ old('status', $sop?->status ?? 'draft') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('description', $sop?->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Why') }}</label><textarea name="why" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('why', $sop?->why) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Who') }}</label><textarea name="who" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('who', $sop?->who) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('When') }}</label><textarea name="when" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('when', $sop?->when) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Input') }}</label><textarea name="input" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('input', $sop?->input) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Output') }}</label><textarea name="output" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('output', $sop?->output) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Risks') }}</label><textarea name="risks" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('risks', $sop?->risks) }}</textarea></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Tools') }}</label>
                <textarea name="tools" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('tools', $sop?->tools) }}</textarea>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Steps') }}</h2>
                <button type="button" @click="steps.push({id:'',title:'',description:''})" class="text-sm font-semibold text-[#ff9200] hover:text-orange-700">+ {{ __('Add step') }}</button>
            </div>
            <template x-for="(step, index) in steps" :key="index">
                <div class="rounded-xl border border-slate-200 p-4">
                    <input type="hidden" :name="`steps[${index}][id]`" x-model="step.id">
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-slate-700">{{ __('Step title') }}</label>
                        <input type="text" :name="`steps[${index}][title]`" x-model="step.title" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                        <textarea :name="`steps[${index}][description]`" x-model="step.description" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
                    </div>
                    <button type="button" @click="steps.splice(index,1)" class="mt-2 text-xs text-red-600 hover:underline">{{ __('Remove') }}</button>
                </div>
            </template>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Checklist') }}</h2>
                <button type="button" @click="checklist.push({id:'',text:'',is_required:true})" class="text-sm font-semibold text-[#ff9200] hover:text-orange-700">+ {{ __('Add item') }}</button>
            </div>
            <template x-for="(item, index) in checklist" :key="index">
                <div class="flex items-start gap-3 rounded-xl border border-slate-200 p-3">
                    <input type="hidden" :name="`checklist[${index}][id]`" x-model="item.id">
                    <div class="flex-1">
                        <input type="text" :name="`checklist[${index}][text]`" x-model="item.text" placeholder="{{ __('Task description') }}" class="block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <label class="flex items-center gap-1 text-sm text-slate-600">
                        <input type="checkbox" :name="`checklist[${index}][is_required]`" value="1" :checked="item.is_required" class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]"> {{ __('Required') }}
                    </label>
                    <button type="button" @click="checklist.splice(index,1)" class="text-xs text-red-600 hover:underline">{{ __('Remove') }}</button>
                </div>
            </template>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Knowledge Check Quiz') }}</h2>
                <button type="button" @click="quizzes.push({id:'',question:'',answer_type:'single',options:'',correct_answer:'',explanation:''})" class="text-sm font-semibold text-[#ff9200] hover:text-orange-700">+ {{ __('Add question') }}</button>
            </div>
            <template x-for="(quiz, index) in quizzes" :key="index">
                <div class="rounded-xl border border-slate-200 p-4 space-y-3">
                    <input type="hidden" :name="`quizzes[${index}][id]`" x-model="quiz.id">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Question') }}</label>
                        <input type="text" :name="`quizzes[${index}][question]`" x-model="quiz.question" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Answer type') }}</label>
                            <select :name="`quizzes[${index}][answer_type]`" x-model="quiz.answer_type" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <option value="single">{{ __('Single choice') }}</option>
                                <option value="multiple">{{ __('Multiple choice') }}</option>
                                <option value="text">{{ __('Free text') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Correct answer') }}</label>
                            <input type="text" :name="`quizzes[${index}][correct_answer]`" x-model="quiz.correct_answer" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Options (one per line, for choice questions)') }}</label>
                        <textarea :name="`quizzes[${index}][options]`" x-model="quiz.options" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Explanation') }}</label>
                        <textarea :name="`quizzes[${index}][explanation]`" x-model="quiz.explanation" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-[#ff9200] focus:ring-[#ff9200]"></textarea>
                    </div>
                    <button type="button" @click="quizzes.splice(index,1)" class="text-xs text-red-600 hover:underline">{{ __('Remove') }}</button>
                </div>
            </template>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">{{ $sop ? __('Update SOP') : __('Create SOP') }}</button>
        </div>
    </form>
</div>
@endsection
