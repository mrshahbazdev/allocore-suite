<div>
    @include('auditpro::partials.nav')

    <div class="mb-6 flex items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Audit templates') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Build reusable pillars and questions for your team.') }}</p>
        </div>
        <button wire:click="create" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New template') }}</button>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Search templates') }}" class="w-full rounded-lg border-slate-300 text-sm sm:max-w-sm">
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($templates as $template)
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-slate-900">{{ $template->name }}</h2>
                            @if ($template->is_default)
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ __('Default') }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $template->description }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ trans_choice(':count pillar|:count pillars', $template->pillars_count, ['count' => $template->pillars_count]) }} · {{ trans_choice(':count question|:count questions', $template->questions_count, ['count' => $template->questions_count]) }} · {{ trans_choice(':count audit|:count audits', $template->audits_count, ['count' => $template->audits_count]) }}</p>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <a href="{{ route('audit.templates.builder', $template) }}" class="font-medium text-indigo-600 hover:underline">{{ __('Build') }}</a>
                        <button wire:click="edit({{ $template->id }})" class="text-slate-600 hover:underline">{{ __('Edit') }}</button>
                        <button wire:click="delete({{ $template->id }})" wire:confirm="{{ __('Delete this template?') }}" class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
                    </div>
                </div>
            @empty
                <p class="p-10 text-center text-sm text-slate-500">{{ __('No matching templates.') }}</p>
            @endforelse
        </div>
        <div class="border-t border-slate-200 p-4">{{ $templates->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <form wire:submit="save" class="w-full max-w-lg space-y-4 rounded-xl bg-white p-6 shadow-xl">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $templateId ? __('Edit template') : __('New template') }}</h2>
                    <p class="text-sm text-slate-500">{{ __('Templates belong only to the current team.') }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                    <input wire:model="name" class="mt-1 w-full rounded-lg border-slate-300">
                    @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <textarea wire:model="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                    @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">{{ __('Cancel') }}</button>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    @endif
</div>
