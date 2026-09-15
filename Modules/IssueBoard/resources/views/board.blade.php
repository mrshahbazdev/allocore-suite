<div>
    {{-- Filter --}}
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <div class="relative">
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ __('issueboard::issueboard.search_placeholder') }}"
                   class="w-64 rounded-md border-slate-300 bg-white py-2 pl-3 pr-3 text-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500">
        </div>

        @if ($projects->isNotEmpty())
            <select wire:model.live="projectId"
                    class="rounded-md border-slate-300 bg-white py-2 text-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="">{{ __('issueboard::issueboard.all_projects') }}</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        @endif

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" wire:model.live="onlyMine"
                   class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
            {{ __('issueboard::issueboard.only_mine') }}
        </label>

        @if ($hasFilters)
            <button type="button" wire:click="resetFilters"
                    class="text-sm text-slate-500 underline underline-offset-4 hover:text-slate-800">
                {{ __('issueboard::issueboard.reset_filters') }}
            </button>
        @endif

        <div wire:loading.delay class="text-sm text-slate-400">
            {{ __('issueboard::issueboard.loading') }}
        </div>
    </div>

    {{-- Spalten --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($statuses as $status)
            @php $column = $issues->get($status->value, collect()); @endphp

            <section class="flex flex-col rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                <div class="rounded-t-lg border-t-4 px-4 py-3" style="border-color: {{ $status->color() }}">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">{{ $status->label() }}</h2>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium"
                              style="background: {{ $status->tint() }}; color: {{ $status->color() }}">
                            {{ $column->count() }}
                        </span>
                    </div>
                </div>

                <div class="issue-column flex min-h-[140px] flex-1 flex-col gap-3 p-3"
                     data-status="{{ $status->value }}"
                     x-data
                     x-init="
                        new Sortable($el, {
                            group: 'issues',
                            animation: 150,
                            ghostClass: 'opacity-40',
                            draggable: '[data-issue-id]',
                            onEnd(evt) {
                                const status = evt.to.dataset.status;
                                const ids = Array.from(evt.to.querySelectorAll('[data-issue-id]'))
                                    .map(el => parseInt(el.dataset.issueId));
                                $wire.moveCard(parseInt(evt.item.dataset.issueId), status, ids);
                            }
                        })
                     ">
                    @forelse ($column as $issue)
                        @include('issueboard::partials.card', ['issue' => $issue])
                    @empty
                        <p class="px-1 py-6 text-center text-xs text-slate-400">
                            {{ __('issueboard::issueboard.empty_column') }}
                        </p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
