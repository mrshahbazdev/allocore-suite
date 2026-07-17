@extends('layouts.shell')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">{{ __('cms.pages') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('cms.pages_description') }}</p>
                </div>
                <a href="{{ route('admin.pages.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('cms.new_page') }}</a>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('cms.all_pages') }}</h3>
                        <button type="button" id="save-order" class="hidden rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('cms.save_order') }}</button>
                    </div>
                </div>

                <form id="order-form" method="POST" action="{{ route('admin.pages.reorder') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="order" id="order-input">
                </form>

                <ul id="page-list" class="divide-y divide-slate-200">
                    @forelse ($pages as $page)
                        @php($translation = $page->translation())
                        <li class="group flex items-center justify-between gap-4 px-6 py-4 hover:bg-slate-50" data-id="{{ $page->id }}" draggable="true">
                            <div class="flex items-center gap-4">
                                <svg class="h-5 w-5 cursor-move text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                                <div>
                                    <div class="font-medium text-slate-900">{{ $translation?->title ?: $page->slug }}</div>
                                    <div class="text-sm text-slate-500">/{{ $translation?->slug ?: $page->slug }} · <span class="uppercase text-xs">{{ __("cms.type_{$page->type}") }}</span></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($page->is_published)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">{{ __('cms.published') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ __('cms.draft') }}</span>
                                @endif
                                <a href="{{ route('admin.pages.edit', $page) }}" class="rounded-lg px-3 py-1.5 text-sm font-medium text-indigo-600 hover:bg-indigo-50">{{ __('cms.edit') }}</a>
                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('{{ __('cms.delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50">{{ __('cms.delete') }}</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-slate-500">{{ __('cms.no_pages') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <script>
        const list = document.getElementById('page-list');
        const saveButton = document.getElementById('save-order');
        const orderForm = document.getElementById('order-form');
        const orderInput = document.getElementById('order-input');

        if (list) {
            let dragged;

            list.querySelectorAll('li[data-id]').forEach(item => {
                item.addEventListener('dragstart', e => {
                    dragged = item;
                    item.classList.add('opacity-50');
                });

                item.addEventListener('dragend', () => {
                    item.classList.remove('opacity-50');
                    dragged = null;
                    saveButton.classList.remove('hidden');
                });

                item.addEventListener('dragover', e => {
                    e.preventDefault();
                    if (! dragged || dragged === item) return;
                    const bounding = item.getBoundingClientRect();
                    const offset = bounding.y + (bounding.height / 2);
                    if (e.clientY - offset > 0) {
                        item.after(dragged);
                    } else {
                        item.before(dragged);
                    }
                });
            });

            saveButton.addEventListener('click', () => {
                const ids = Array.from(list.querySelectorAll('li[data-id]')).map(li => li.dataset.id);
                orderInput.value = JSON.stringify(ids);
                orderForm.submit();
            });
        }
    </script>
@endsection
