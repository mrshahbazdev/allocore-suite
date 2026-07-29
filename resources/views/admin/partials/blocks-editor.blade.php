@php($name = $name ?? 'blocks')
@php($items = $blocks ?? [])

<div x-data="{
    blocks: @js($items ?: []),
    dragFrom: null,
    dragOver: null,
    defaults(type) {
        const style = { bg: '', text_color: '', text_align: 'center', padding: 'medium', container: 'default', rounded: false };
        if (type === 'hero') { style.bg = '#0f172a'; style.text_color = '#ffffff'; }
        if (type === 'cta') { style.bg = '#4f46e5'; style.text_color = '#ffffff'; }
        const d = { type: type, enabled: true, animation: type === 'hero' ? 'fade-up' : '', style: style };
        if (['features','faq'].includes(type)) d.items = [];
        return d;
    },
    add(type) { this.blocks.push(this.defaults(type)); },
    move(index, dir) { const target = index + dir; if (target >= 0 && target < this.blocks.length) { const tmp = this.blocks[index]; this.blocks[index] = this.blocks[target]; this.blocks[target] = tmp; } },
    remove(index) { this.blocks.splice(index, 1); },
    dragStart(index) { this.dragFrom = index; },
    dragEnter(index) { this.dragOver = index; },
    drop(targetIndex) { if (this.dragFrom === null || this.dragFrom === targetIndex) { this.dragFrom = null; this.dragOver = null; return; } const item = this.blocks[this.dragFrom]; const reordered = this.blocks.filter((_, i) => i !== this.dragFrom); reordered.splice(targetIndex, 0, item); this.blocks = reordered; this.dragFrom = null; this.dragOver = null; }
}">
    <div class="space-y-4">
        <template x-for="(block, index) in blocks" :key="index">
            <div
                class="rounded-xl border bg-white p-4 shadow-sm transition"
                :class="{ 'border-indigo-400 ring-1 ring-indigo-400': dragOver === index, 'border-slate-200': dragOver !== index }"
                draggable="true"
                @dragstart="dragStart(index)"
                @dragover.prevent="dragEnter(index)"
                @drop.prevent="drop(index)"
                @dragend="dragFrom = null; dragOver = null"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="cursor-grab rounded bg-slate-100 px-2 py-1 text-xs text-slate-500">⋮⋮</span>
                        <span class="text-sm font-semibold uppercase tracking-wide text-slate-500" x-text="block.type"></span>
                        <label class="ml-2 flex items-center gap-1 text-xs text-slate-600">
                            <input type="hidden" :name="`{{ $name }}[${index}][enabled]`" value="0">
                            <input type="checkbox" :name="`{{ $name }}[${index}][enabled]`" value="1" x-model="block.enabled" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Enabled') }}
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="move(index, -1)" class="rounded bg-slate-100 px-2 py-1 text-xs hover:bg-slate-200">{{ __('Up') }}</button>
                        <button type="button" @click="move(index, 1)" class="rounded bg-slate-100 px-2 py-1 text-xs hover:bg-slate-200">{{ __('Down') }}</button>
                        <button type="button" @click="remove(index)" class="rounded bg-red-100 px-2 py-1 text-xs text-red-700 hover:bg-red-200">{{ __('Remove') }}</button>
                    </div>
                </div>

                <input type="hidden" :name="`{{ $name }}[${index}][type]`" x-model="block.type">

                <div class="grid gap-3 md:grid-cols-2">
                    <template x-if="block.type === 'hero'">
                        <div class="col-span-2 grid gap-3 md:grid-cols-2">
                            <input type="text" :name="`{{ $name }}[${index}][heading]`" x-model="block.heading" placeholder="Heading" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][subheading]`" x-model="block.subheading" placeholder="Subheading" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][image]`" x-model="block.image" placeholder="Background image URL" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][cta_text]`" x-model="block.cta_text" placeholder="CTA text" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][cta_url]`" x-model="block.cta_url" placeholder="CTA URL" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </template>

                    <template x-if="block.type === 'features'">
                        <div class="col-span-2 space-y-2">
                            <input type="text" :name="`{{ $name }}[${index}][title]`" x-model="block.title" placeholder="Section title" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <template x-for="(item, i) in (block.items || [])" :key="i">
                                <div class="flex gap-2">
                                    <input type="text" :name="`{{ $name }}[${index}][items][${i}][title]`" x-model="item.title" placeholder="Feature title" class="w-1/3 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][items][${i}][description]`" x-model="item.description" placeholder="Feature description" class="flex-1 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <button type="button" @click="block.items.splice(i, 1)" class="rounded bg-slate-100 px-2 py-1 text-xs">{{ __('Remove') }}</button>
                                </div>
                            </template>
                            <button type="button" @click="(block.items || []).push({title: '', description: ''})" class="rounded bg-slate-100 px-3 py-1 text-xs">{{ __('Add feature') }}</button>
                        </div>
                    </template>

                    <template x-if="block.type === 'text'">
                        <div class="col-span-2">
                            <textarea :name="`{{ $name }}[${index}][content]`" x-model="block.content" rows="4" placeholder="HTML content" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </template>

                    <template x-if="block.type === 'image'">
                        <div class="col-span-2 grid gap-3 md:grid-cols-2">
                            <input type="text" :name="`{{ $name }}[${index}][src]`" x-model="block.src" placeholder="Image URL" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][alt]`" x-model="block.alt" placeholder="Alt text" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </template>

                    <template x-if="block.type === 'cta'">
                        <div class="col-span-2 grid gap-3 md:grid-cols-2">
                            <input type="text" :name="`{{ $name }}[${index}][title]`" x-model="block.title" placeholder="Title" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][text]`" x-model="block.text" placeholder="Text" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][button_text]`" x-model="block.button_text" placeholder="Button text" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][button_url]`" x-model="block.button_url" placeholder="Button URL" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </template>

                    <template x-if="block.type === 'faq'">
                        <div class="col-span-2 space-y-2">
                            <input type="text" :name="`{{ $name }}[${index}][title]`" x-model="block.title" placeholder="Section title" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <template x-for="(item, i) in (block.items || [])" :key="i">
                                <div class="flex gap-2">
                                    <input type="text" :name="`{{ $name }}[${index}][items][${i}][question]`" x-model="item.question" placeholder="Question" class="w-1/3 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][items][${i}][answer]`" x-model="item.answer" placeholder="Answer" class="flex-1 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <button type="button" @click="block.items.splice(i, 1)" class="rounded bg-slate-100 px-2 py-1 text-xs">{{ __('Remove') }}</button>
                                </div>
                            </template>
                            <button type="button" @click="(block.items || []).push({question: '', answer: ''})" class="rounded bg-slate-100 px-3 py-1 text-xs">{{ __('Add FAQ') }}</button>
                        </div>
                    </template>
                </div>

                <details class="mt-4 rounded-lg border border-slate-100 bg-slate-50">
                    <summary class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Advanced options') }}</summary>
                    <div class="grid gap-3 p-3 md:grid-cols-3">
                        <label class="block text-xs text-slate-600">
                            {{ __('Background color') }}
                            <input type="text" :name="`{{ $name }}[${index}][style][bg]`" x-model="block.style.bg" placeholder="#f8fafc or tailwind class" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block text-xs text-slate-600">
                            {{ __('Text color') }}
                            <input type="text" :name="`{{ $name }}[${index}][style][text_color]`" x-model="block.style.text_color" placeholder="#0f172a or tailwind class" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block text-xs text-slate-600">
                            {{ __('Text align') }}
                            <select :name="`{{ $name }}[${index}][style][text_align]`" x-model="block.style.text_align" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="left">{{ __('Left') }}</option>
                                <option value="center">{{ __('Center') }}</option>
                                <option value="right">{{ __('Right') }}</option>
                            </select>
                        </label>
                        <label class="block text-xs text-slate-600">
                            {{ __('Padding') }}
                            <select :name="`{{ $name }}[${index}][style][padding]`" x-model="block.style.padding" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="small">{{ __('Small') }}</option>
                                <option value="medium">{{ __('Medium') }}</option>
                                <option value="large">{{ __('Large') }}</option>
                            </select>
                        </label>
                        <label class="block text-xs text-slate-600">
                            {{ __('Container width') }}
                            <select :name="`{{ $name }}[${index}][style][container]`" x-model="block.style.container" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="default">{{ __('Default') }}</option>
                                <option value="max-w-7xl">{{ __('Large') }}</option>
                                <option value="max-w-5xl">{{ __('Medium') }}</option>
                                <option value="max-w-3xl">{{ __('Small') }}</option>
                                <option value="full">{{ __('Full width') }}</option>
                            </select>
                        </label>
                        <label class="block text-xs text-slate-600">
                            {{ __('Animation') }}
                            <select :name="`{{ $name }}[${index}][animation]`" x-model="block.animation" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('None') }}</option>
                                <option value="fade-up">{{ __('Fade up') }}</option>
                                <option value="fade-in">{{ __('Fade in') }}</option>
                                <option value="slide-left">{{ __('Slide left') }}</option>
                                <option value="slide-right">{{ __('Slide right') }}</option>
                                <option value="zoom-in">{{ __('Zoom in') }}</option>
                            </select>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-600 md:col-span-3">
                            <input type="hidden" :name="`{{ $name }}[${index}][style][rounded]`" value="0">
                            <input type="checkbox" :name="`{{ $name }}[${index}][style][rounded]`" value="1" x-model="block.style.rounded" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Rounded inner container') }}
                        </label>
                    </div>
                </details>
            </div>
        </template>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <select id="new-block-{{ str_replace(['[',']'], ['-',''], $name) }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="hero">{{ __('Hero') }}</option>
            <option value="features">{{ __('Features') }}</option>
            <option value="text">{{ __('Text') }}</option>
            <option value="image">{{ __('Image') }}</option>
            <option value="cta">{{ __('Call to action') }}</option>
            <option value="faq">{{ __('FAQ') }}</option>
        </select>
        <button type="button" @click="add(document.getElementById('new-block-{{ str_replace(['[',']'], ['-',''], $name) }}').value)" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">{{ __('Add block') }}</button>
    </div>
</div>
