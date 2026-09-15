<div class="mx-auto max-w-3xl">
    <h1 class="text-xl font-semibold tracking-tight text-slate-900">
        {{ $issue?->exists ? __('issueboard::issueboard.edit_issue') : __('issueboard::issueboard.new_issue') }}
    </h1>

    <div class="mt-5 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">

        <div>
            <label for="title" class="block text-sm font-medium text-slate-700">
                {{ __('issueboard::issueboard.fields.title') }}
            </label>
            <input id="title" type="text" wire:model="title"
                   class="mt-1.5 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-slate-700">
                {{ __('issueboard::issueboard.fields.description') }}
            </label>
            <textarea id="description" wire:model="description" rows="5"
                      class="mt-1.5 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"></textarea>
            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="solution" class="block text-sm font-medium text-slate-700">
                {{ __('issueboard::issueboard.fields.suggested_solution') }}
            </label>
            <textarea id="solution" wire:model="suggested_solution" rows="3"
                      class="mt-1.5 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"></textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="project" class="block text-sm font-medium text-slate-700">
                    {{ __('issueboard::issueboard.fields.project') }}
                </label>
                <select id="project" wire:model="project_id"
                        class="mt-1.5 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">{{ __('issueboard::issueboard.all_projects') }}</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="assignee" class="block text-sm font-medium text-slate-700">
                    {{ __('issueboard::issueboard.fields.assignee') }}
                </label>
                <select id="assignee" wire:model="assigned_to"
                        class="mt-1.5 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">{{ __('issueboard::issueboard.unassigned') }}</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="priority" class="block text-sm font-medium text-slate-700">
                    {{ __('issueboard::issueboard.priority_label') }}
                </label>
                <select id="priority" wire:model="priority"
                        class="mt-1.5 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="1">{{ __('issueboard::issueboard.priority.1') }}</option>
                    <option value="2">{{ __('issueboard::issueboard.priority.2') }}</option>
                    <option value="3">{{ __('issueboard::issueboard.priority.3') }}</option>
                </select>
            </div>

            <div>
                <label for="due" class="block text-sm font-medium text-slate-700">
                    {{ __('issueboard::issueboard.due_date') }}
                </label>
                <input id="due" type="date" wire:model="due_date"
                       class="mt-1.5 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
        </div>

        {{-- Links --}}
        <div>
            <p class="text-sm font-medium text-slate-700">{{ __('issueboard::issueboard.fields.links') }}</p>

            <div class="mt-2 space-y-2">
                @foreach ($links as $index => $link)
                    <div class="flex gap-2" wire:key="link-{{ $index }}">
                        <input type="text" wire:model="links.{{ $index }}.label"
                               placeholder="{{ __('issueboard::issueboard.fields.link_label') }}"
                               class="w-1/3 rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <input type="url" wire:model="links.{{ $index }}.url" placeholder="https://"
                               class="flex-1 rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <button type="button" wire:click="removeLink({{ $index }})"
                                class="px-2 text-slate-400 hover:text-red-700" aria-label="{{ __('issueboard::issueboard.delete') }}">✕</button>
                    </div>
                    @error("links.{$index}.url") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @endforeach
            </div>

            <button type="button" wire:click="addLink"
                    class="mt-2 text-sm text-slate-600 underline underline-offset-4 hover:text-slate-900">
                {{ __('issueboard::issueboard.add_link') }}
            </button>
        </div>

        {{-- Dateien --}}
        <div>
            <label for="files" class="block text-sm font-medium text-slate-700">
                {{ __('issueboard::issueboard.fields.files') }}
            </label>
            <input id="files" type="file" wire:model="files" multiple
                   class="mt-1.5 text-sm text-slate-600 file:mr-3 file:rounded file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:text-slate-700">
            <p class="mt-1 text-xs text-slate-500">
                {{ __('issueboard::issueboard.upload_hint', ['size' => round(config('issueboard.max_upload_kb') / 1024)]) }}
            </p>
            @error('files.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div wire:loading wire:target="files" class="mt-1 text-xs text-slate-500">
                {{ __('issueboard::issueboard.uploading') }}
            </div>

            @if ($files)
                <ul class="mt-2 space-y-1 text-sm text-slate-600">
                    @foreach ($files as $index => $file)
                        <li wire:key="file-{{ $index }}" class="flex items-center gap-2">
                            {{ $file->getClientOriginalName() }}
                            <button type="button" wire:click="removeFile({{ $index }})"
                                    class="text-slate-400 hover:text-red-700">✕</button>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($issue?->exists && $issue->attachments->isNotEmpty())
                <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
                    @foreach ($issue->attachments as $attachment)
                        <div class="relative" wire:key="att-{{ $attachment->id }}">
                            @if ($attachment->is_image)
                                <img src="{{ $attachment->url }}" alt="{{ $attachment->original_name }}"
                                     class="h-20 w-full rounded border border-slate-200 object-cover">
                            @else
                                <div class="flex h-20 items-center justify-center rounded border border-slate-200 bg-slate-50 px-1 text-center text-[11px] text-slate-600">
                                    {{ $attachment->original_name }}
                                </div>
                            @endif
                            <button type="button" wire:click="removeAttachment({{ $attachment->id }})"
                                    class="absolute -right-1.5 -top-1.5 rounded-full bg-white px-1.5 text-xs text-slate-500 shadow ring-1 ring-slate-200 hover:text-red-700">✕</button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Kontakt --}}
        <fieldset class="border-t border-slate-200 pt-5">
            <legend class="text-sm font-medium text-slate-700">{{ __('issueboard::issueboard.contact') }}</legend>

            <div class="mt-3 grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="cname" class="block text-xs text-slate-500">{{ __('issueboard::issueboard.fields.contact_name') }}</label>
                    <input id="cname" type="text" wire:model="contact_name"
                           class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
                <div>
                    <label for="cmail" class="block text-xs text-slate-500">{{ __('issueboard::issueboard.fields.contact_email') }}</label>
                    <input id="cmail" type="email" wire:model="contact_email"
                           class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @error('contact_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cphone" class="block text-xs text-slate-500">{{ __('issueboard::issueboard.fields.contact_phone') }}</label>
                    <input id="cphone" type="text" wire:model="contact_phone"
                           class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
            </div>
        </fieldset>

        <div class="flex items-center gap-3 border-t border-slate-200 pt-5">
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">
                {{ __('issueboard::issueboard.save') }}
            </button>
            <a href="{{ route('issueboard.index') }}" class="text-sm text-slate-500 hover:text-slate-800">
                {{ __('issueboard::issueboard.cancel') }}
            </a>
        </div>
    </div>
</div>
