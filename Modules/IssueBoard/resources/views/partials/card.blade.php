@php
    $thumb = $issue->relationLoaded('attachments')
        ? $issue->attachments->firstWhere('is_image', true)
        : null;
@endphp

<article data-issue-id="{{ $issue->id }}"
         wire:key="issue-{{ $issue->id }}"
         class="group cursor-grab rounded-md border border-slate-200 bg-white p-3 shadow-sm transition hover:border-slate-300 active:cursor-grabbing">

    <a href="{{ route('issueboard.show', $issue) }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
        <div class="flex items-start gap-2">
            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" style="background: {{ $issue->status->color() }}"></span>
            <h3 class="text-sm font-medium leading-snug text-slate-900">{{ $issue->title }}</h3>
        </div>

        @if ($thumb)
            <img src="{{ $thumb->url }}" alt=""
                 class="mt-2 h-24 w-full rounded object-cover" loading="lazy">
        @endif

        <p class="mt-2 text-xs text-slate-500">
            {{ $issue->project?->name ?? __('issueboard::issueboard.all_projects') }}
        </p>
    </a>

    <div class="mt-2.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
        @if ($issue->assignee)
            <span class="font-medium text-slate-600">{{ $issue->assignee->name }}</span>
        @endif

        @if ($issue->due_date)
            <span @class(['text-red-600 font-medium' => $issue->is_overdue])>
                {{ $issue->due_date->format('d.m.Y') }}
            </span>
        @endif

        @if ($issue->priority === 1)
            <span class="rounded bg-red-50 px-1.5 py-0.5 font-medium text-red-700">
                {{ __('issueboard::issueboard.priority.1') }}
            </span>
        @endif

        @if ($issue->open_questions_count)
            <span class="rounded bg-amber-50 px-1.5 py-0.5 font-medium text-amber-700">
                {{ trans_choice('issueboard::issueboard.open_questions', $issue->open_questions_count, ['count' => $issue->open_questions_count]) }}
            </span>
        @endif

        @if ($issue->comments_count)
            <span title="{{ __('issueboard::issueboard.comments') }}">💬 {{ $issue->comments_count }}</span>
        @endif

        @if ($issue->attachments_count)
            <span title="{{ __('issueboard::issueboard.attachments') }}">📎 {{ $issue->attachments_count }}</span>
        @endif
    </div>
</article>
