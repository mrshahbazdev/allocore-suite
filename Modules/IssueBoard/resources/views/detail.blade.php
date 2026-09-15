<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">

    {{-- Hauptspalte --}}
    <div class="space-y-6">
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-500">
                        #{{ $issue->id }} ·
                        {{ $issue->project?->name ?? __('issueboard::issueboard.all_projects') }} ·
                        {{ $issue->created_at->format('d.m.Y') }}
                    </p>
                    <h1 class="mt-1 text-xl font-semibold leading-tight text-slate-900">{{ $issue->title }}</h1>
                </div>

                @can('update', $issue)
                    <a href="{{ route('issueboard.edit', $issue) }}"
                       class="shrink-0 rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
                        {{ __('issueboard::issueboard.edit') }}
                    </a>
                @endcan
            </div>

            @if ($issue->description)
                <div class="prose prose-sm mt-4 max-w-none text-slate-700">
                    {!! nl2br(e($issue->description)) !!}
                </div>
            @endif

            @if ($issue->suggested_solution)
                <div class="mt-5 rounded-md border-l-2 border-slate-300 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold text-slate-500">{{ __('issueboard::issueboard.suggested_solution') }}</p>
                    <div class="mt-1 text-sm text-slate-700">{!! nl2br(e($issue->suggested_solution)) !!}</div>
                </div>
            @endif

            @if ($issue->links->isNotEmpty())
                <ul class="mt-5 space-y-1.5">
                    @foreach ($issue->links as $link)
                        <li>
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                               class="text-sm text-blue-700 underline underline-offset-2 hover:text-blue-900">
                                {{ $link->display_label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($issue->attachments->isNotEmpty())
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($issue->attachments as $attachment)
                        <a href="{{ $attachment->url }}" target="_blank" rel="noopener noreferrer"
                           class="block overflow-hidden rounded border border-slate-200 hover:border-slate-400">
                            @if ($attachment->is_image)
                                <img src="{{ $attachment->url }}" alt="{{ $attachment->original_name }}"
                                     class="h-28 w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-28 items-center justify-center bg-slate-50 px-2 text-center text-xs text-slate-600">
                                    {{ $attachment->original_name }}
                                </div>
                            @endif
                            <p class="truncate px-2 py-1 text-[11px] text-slate-500">{{ $attachment->readable_size }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Rueckfragen und Kommentare --}}
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('issueboard::issueboard.comments') }}</h2>

            <ul class="mt-4 space-y-5">
                @forelse ($comments as $comment)
                    <li wire:key="comment-{{ $comment->id }}"
                        @class([
                            'rounded-md px-4 py-3',
                            'bg-amber-50 ring-1 ring-amber-200' => $comment->is_open_question,
                            'bg-slate-50' => ! $comment->is_open_question,
                        ])>
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-sm font-medium text-slate-900">
                                {{ $comment->user->name }}
                                @if ($comment->is_question)
                                    <span class="ml-1 text-xs font-normal text-amber-700">
                                        {{ $comment->answered_at
                                            ? __('issueboard::issueboard.answered')
                                            : __('issueboard::issueboard.open_question') }}
                                    </span>
                                @endif
                            </p>
                            <time class="text-xs text-slate-400">{{ $comment->created_at->format('d.m.Y H:i') }}</time>
                        </div>

                        <div class="mt-1.5 text-sm text-slate-700">{!! nl2br(e($comment->body)) !!}</div>

                        @if ($comment->attachments->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($comment->attachments as $attachment)
                                    <a href="{{ $attachment->url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-xs text-blue-700 underline underline-offset-2">
                                        {{ $attachment->original_name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-2 flex gap-4 text-xs">
                            <button type="button" wire:click="startReply({{ $comment->id }})"
                                    class="text-slate-500 hover:text-slate-900">
                                {{ __('issueboard::issueboard.reply') }}
                            </button>

                            @if ($comment->is_open_question)
                                <button type="button" wire:click="markAnswered({{ $comment->id }})"
                                        class="text-slate-500 hover:text-slate-900">
                                    {{ __('issueboard::issueboard.mark_answered') }}
                                </button>
                            @endif

                            @if ($comment->user_id === auth()->id())
                                <button type="button" wire:click="deleteComment({{ $comment->id }})"
                                        wire:confirm="{{ __('issueboard::issueboard.confirm_delete_comment') }}"
                                        class="text-slate-400 hover:text-red-700">
                                    {{ __('issueboard::issueboard.delete') }}
                                </button>
                            @endif
                        </div>

                        @if ($comment->replies->isNotEmpty())
                            <ul class="mt-3 space-y-3 border-l border-slate-300 pl-4">
                                @foreach ($comment->replies as $reply)
                                    <li wire:key="reply-{{ $reply->id }}">
                                        <div class="flex items-baseline justify-between gap-3">
                                            <p class="text-sm font-medium text-slate-900">{{ $reply->user->name }}</p>
                                            <time class="text-xs text-slate-400">{{ $reply->created_at->format('d.m.Y H:i') }}</time>
                                        </div>
                                        <div class="mt-1 text-sm text-slate-700">{!! nl2br(e($reply->body)) !!}</div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @empty
                    <li class="py-4 text-sm text-slate-500">{{ __('issueboard::issueboard.no_comments') }}</li>
                @endforelse
            </ul>

            {{-- Neuer Kommentar --}}
            <div class="mt-6 border-t border-slate-200 pt-5">
                @if ($replyTo)
                    <p class="mb-2 text-xs text-slate-500">
                        {{ __('issueboard::issueboard.replying') }}
                        <button type="button" wire:click="cancelReply" class="underline underline-offset-2">
                            {{ __('issueboard::issueboard.cancel') }}
                        </button>
                    </p>
                @endif

                <textarea wire:model="body" rows="3"
                          placeholder="{{ __('issueboard::issueboard.comment_placeholder') }}"
                          class="w-full rounded-md border-slate-300 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500"></textarea>
                @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-3 flex flex-wrap items-center gap-4">
                    <input type="file" wire:model="commentFiles" multiple
                           class="text-xs text-slate-600 file:mr-3 file:rounded file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:text-slate-700">

                    @if (! $replyTo)
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" wire:model="isQuestion"
                                   class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            {{ __('issueboard::issueboard.as_question') }}
                        </label>
                    @endif

                    <button type="button" wire:click="addComment" wire:loading.attr="disabled"
                            class="ml-auto rounded-md bg-slate-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">
                        {{ __('issueboard::issueboard.post_comment') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Seitenleiste --}}
    <aside class="space-y-4">
        <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold text-slate-500">{{ __('issueboard::issueboard.status_label') }}</p>

            <div class="mt-3 space-y-2">
                @foreach ($statuses as $status)
                    @can('moveTo', [$issue, $status])
                        <button type="button" wire:click="setStatus('{{ $status->value }}')"
                                @class([
                                    'flex w-full items-center gap-2.5 rounded-md border px-3 py-2 text-sm transition',
                                    'border-slate-900 font-medium' => $issue->status === $status,
                                    'border-slate-200 text-slate-600 hover:border-slate-400' => $issue->status !== $status,
                                ])
                                @style(['background: ' . $status->tint() => $issue->status === $status])>
                            <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $status->color() }}"></span>
                            {{ $status->label() }}
                        </button>
                    @endcan
                @endforeach
            </div>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">{{ __('issueboard::issueboard.reported_by') }}</dt>
                    <dd class="text-slate-800">{{ $issue->author?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">{{ __('issueboard::issueboard.assignee') }}</dt>
                    <dd class="text-slate-800">{{ $issue->assignee?->name ?? __('issueboard::issueboard.unassigned') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">{{ __('issueboard::issueboard.priority_label') }}</dt>
                    <dd class="text-slate-800">{{ $issue->priority_label }}</dd>
                </div>
                @if ($issue->due_date)
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('issueboard::issueboard.due_date') }}</dt>
                        <dd @class(['text-red-600 font-medium' => $issue->is_overdue, 'text-slate-800' => ! $issue->is_overdue])>
                            {{ $issue->due_date->format('d.m.Y') }}
                        </dd>
                    </div>
                @endif
                @if ($issue->contact_line)
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('issueboard::issueboard.contact') }}</dt>
                        <dd class="text-slate-800">
                            {{ $issue->contact_name }}
                            @if ($issue->contact_email)
                                <br><a href="mailto:{{ $issue->contact_email }}" class="text-blue-700 underline underline-offset-2">{{ $issue->contact_email }}</a>
                            @endif
                            @if ($issue->contact_phone)
                                <br><a href="tel:{{ $issue->contact_phone }}" class="text-slate-700">{{ $issue->contact_phone }}</a>
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($history->isNotEmpty())
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold text-slate-500">{{ __('issueboard::issueboard.history') }}</p>
                <ul class="mt-3 space-y-2 text-xs text-slate-600">
                    @foreach ($history as $log)
                        <li>
                            <span class="text-slate-400">{{ $log->created_at->format('d.m. H:i') }}</span>
                            {{ $log->user->name }} →
                            <span style="color: {{ $log->to_status->color() }}">{{ $log->to_status->label() }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </aside>
</div>
