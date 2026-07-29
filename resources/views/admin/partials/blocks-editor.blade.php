@php($name = $name ?? 'blocks')
@php($items = $blocks ?? [])
@php($blockTypes = [
    'hero' => 'Hero',
    'features' => 'Features',
    'text' => 'Text',
    'image' => 'Image',
    'cta' => 'Call to action',
    'faq' => 'FAQ',
    'stats' => 'Stats',
    'testimonials' => 'Testimonials',
    'pricing' => 'Pricing',
    'steps' => 'Steps',
    'logos' => 'Logos',
    'divider' => 'Divider',
    'spacer' => 'Spacer',
])

<div x-data="{
    blocks: @js($items ?: []),
    dragFrom: null,
    dragOver: null,
    defaults(type) {
        const style = { bg: '', text_color: '', text_align: 'center', padding: 'medium', container: 'default', rounded: false, border: false };
        const layout = { columns: 0, gap: 'medium', align: 'stretch' };
        if (type === 'hero') { style.bg = '#0f172a'; style.text_color = '#ffffff'; }
        if (type === 'cta') { style.bg = '#4f46e5'; style.text_color = '#ffffff'; }
        if (type === 'features') { layout.columns = 3; }
        if (type === 'stats') { layout.columns = 4; }
        if (type === 'testimonials') { layout.columns = 3; }
        if (type === 'pricing') { layout.columns = 3; }
        if (type === 'steps') { layout.columns = 4; }
        if (type === 'logos') { layout.columns = 5; }
        const d = { type: type, enabled: true, collapsed: false, animation: type === 'hero' ? 'fade-up' : '', style: style, layout: layout };
        if (['features','faq','stats','testimonials','pricing','steps','logos'].includes(type)) d.items = [];
        return d;
    },
    add(type) { this.blocks.push(this.defaults(type)); },
    duplicate(index) { const copy = JSON.parse(JSON.stringify(this.blocks[index])); this.blocks.splice(index + 1, 0, copy); },
    move(index, dir) { const target = index + dir; if (target >= 0 && target < this.blocks.length) { const tmp = this.blocks[index]; this.blocks[index] = this.blocks[target]; this.blocks[target] = tmp; } },
    remove(index) { this.blocks.splice(index, 1); },
    dragStart(index) { this.dragFrom = index; },
    dragEnter(index) { this.dragOver = index; },
    drop(targetIndex) { if (this.dragFrom === null || this.dragFrom === targetIndex) { this.dragFrom = null; this.dragOver = null; return; } const item = this.blocks[this.dragFrom]; const reordered = this.blocks.filter((_, i) => i !== this.dragFrom); reordered.splice(targetIndex, 0, item); this.blocks = reordered; this.dragFrom = null; this.dragOver = null; },
    ensure(type, fields) { if (! this.block[type]) { this.block[type] = {}; } fields.forEach(f => { if (this.block[type][f] === undefined) { this.block[type][f] = ''; } }); }
}">
    <div class="space-y-4">
        <template x-for="(block, index) in blocks" :key="index">
            <div
                class="rounded-xl border bg-white shadow-sm transition"
                :class="{ 'border-indigo-400 ring-1 ring-indigo-400': dragOver === index, 'border-slate-200': dragOver !== index }"
                draggable="true"
                @dragstart="dragStart(index)"
                @dragover.prevent="dragEnter(index)"
                @drop.prevent="drop(index)"
                @dragend="dragFrom = null; dragOver = null"
            >
                <div class="flex cursor-grab items-center justify-between gap-2 rounded-t-xl border-b border-slate-100 bg-slate-50 px-4 py-3" @click="block.collapsed = !block.collapsed">
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-400">⋮⋮</span>
                        <span class="text-sm font-semibold uppercase tracking-wide text-slate-600" x-text="(block.type || '').replace('-', ' ')"></span>
                        <span class="rounded bg-slate-200 px-2 py-0.5 text-xs text-slate-600" x-text="index + 1"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-1 text-xs text-slate-600" @click.stop>
                            <input type="hidden" :name="`{{ $name }}[${index}][enabled]`" value="0">
                            <input type="checkbox" :name="`{{ $name }}[${index}][enabled]`" value="1" x-model="block.enabled" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Enabled') }}
                        </label>
                        <button type="button" @click.stop="duplicate(index)" class="rounded bg-white px-2 py-1 text-xs text-slate-600 shadow-sm hover:bg-slate-100">{{ __('Duplicate') }}</button>
                        <button type="button" @click.stop="move(index, -1)" class="rounded bg-white px-2 py-1 text-xs text-slate-600 shadow-sm hover:bg-slate-100">{{ __('Up') }}</button>
                        <button type="button" @click.stop="move(index, 1)" class="rounded bg-white px-2 py-1 text-xs text-slate-600 shadow-sm hover:bg-slate-100">{{ __('Down') }}</button>
                        <button type="button" @click.stop="remove(index)" class="rounded bg-red-100 px-2 py-1 text-xs text-red-700 hover:bg-red-200">{{ __('Remove') }}</button>
                    </div>
                </div>

                <div x-show="!block.collapsed" class="p-4 space-y-4" x-cloak>
                    <input type="hidden" :name="`{{ $name }}[${index}][type]`" x-model="block.type">

                    <template x-if="['hero','cta'].includes(block.type)">
                        <div class="grid gap-3 md:grid-cols-2">
                            <template x-if="block.type === 'hero'">
                                <div class="col-span-2 grid gap-3 md:grid-cols-2">
                                    <input type="text" :name="`{{ $name }}[${index}][heading]`" x-model="block.heading" placeholder="{{ __('Heading') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][subheading]`" x-model="block.subheading" placeholder="{{ __('Subheading') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][image]`" x-model="block.image" placeholder="{{ __('Background image URL') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][cta_text]`" x-model="block.cta_text" placeholder="{{ __('CTA text') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][cta_url]`" x-model="block.cta_url" placeholder="{{ __('CTA URL') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </template>
                            <template x-if="block.type === 'cta'">
                                <div class="col-span-2 grid gap-3 md:grid-cols-2">
                                    <input type="text" :name="`{{ $name }}[${index}][title]`" x-model="block.title" placeholder="{{ __('Title') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][text]`" x-model="block.text" placeholder="{{ __('Text') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][button_text]`" x-model="block.button_text" placeholder="{{ __('Button text') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`{{ $name }}[${index}][button_url]`" x-model="block.button_url" placeholder="{{ __('Button URL') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="['features','faq','stats','testimonials','pricing','steps','logos'].includes(block.type)">
                        <div class="space-y-3">
                            <input type="text" :name="`{{ $name }}[${index}][title]`" x-model="block.title" placeholder="{{ __('Section title') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <template x-for="(item, i) in (block.items || [])" :key="i">
                                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-slate-500" x-text="'Item ' + (i + 1)"></span>
                                        <button type="button" @click="block.items.splice(i, 1)" class="rounded bg-white px-2 py-1 text-xs text-red-600 shadow-sm">{{ __('Remove') }}</button>
                                    </div>
                                    <template x-if="block.type === 'features'">
                                        <div class="grid gap-2 md:grid-cols-2">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][title]`" x-model="item.title" placeholder="{{ __('Feature title') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][description]`" x-model="item.description" placeholder="{{ __('Feature description') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </template>
                                    <template x-if="block.type === 'faq'">
                                        <div class="grid gap-2">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][question]`" x-model="item.question" placeholder="{{ __('Question') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][answer]`" x-model="item.answer" placeholder="{{ __('Answer') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </template>
                                    <template x-if="block.type === 'stats'">
                                        <div class="grid gap-2 md:grid-cols-3">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][label]`" x-model="item.label" placeholder="{{ __('Label') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][value]`" x-model="item.value" placeholder="{{ __('Value') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][suffix]`" x-model="item.suffix" placeholder="{{ __('Suffix (e.g. %, +)') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </template>
                                    <template x-if="block.type === 'testimonials'">
                                        <div class="grid gap-2">
                                            <textarea :name="`{{ $name }}[${index}][items][${i}][quote]`" x-model="item.quote" placeholder="Quote" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                            <div class="grid gap-2 md:grid-cols-2">
                                                <input type="text" :name="`{{ $name }}[${index}][items][${i}][author]`" x-model="item.author" placeholder="{{ __('Author') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <input type="text" :name="`{{ $name }}[${index}][items][${i}][role]`" x-model="item.role" placeholder="{{ __('Role / Company') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="block.type === 'pricing'">
                                        <div class="grid gap-2">
                                            <div class="grid gap-2 md:grid-cols-4">
                                                <input type="text" :name="`{{ $name }}[${index}][items][${i}][name]`" x-model="item.name" placeholder="{{ __('Plan name') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <input type="text" :name="`{{ $name }}[${index}][items][${i}][price]`" x-model="item.price" placeholder="{{ __('Price') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <input type="text" :name="`{{ $name }}[${index}][items][${i}][period]`" x-model="item.period" placeholder="{{ __('Period (e.g. month)') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <label class="flex items-center gap-2 text-xs text-slate-600">
                                                    <input type="hidden" :name="`{{ $name }}[${index}][items][${i}][highlighted]`" value="0">
                                                    <input type="checkbox" :name="`{{ $name }}[${index}][items][${i}][highlighted]`" value="1" x-model="item.highlighted" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                    {{ __('Highlight') }}
                                                </label>
                                            </div>
                                            <textarea :name="`{{ $name }}[${index}][items][${i}][features]`" x-model="item.features" placeholder="Features, one per line" rows="3" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                            <div class="grid gap-2 md:grid-cols-2">
                                                <input type="text" :name="`{{ $name }}[${index}][items][${i}][cta_text]`" x-model="item.cta_text" placeholder="{{ __('Button text') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <input type="text" :name="`{{ $name }}[${index}][items][${i}][cta_url]`" x-model="item.cta_url" placeholder="{{ __('Button URL') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="block.type === 'steps'">
                                        <div class="grid gap-2">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][title]`" x-model="item.title" placeholder="{{ __('Step title') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <textarea :name="`{{ $name }}[${index}][items][${i}][description]`" x-model="item.description" placeholder="Step description" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                        </div>
                                    </template>
                                    <template x-if="block.type === 'logos'">
                                        <div class="grid gap-2 md:grid-cols-2">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][name]`" x-model="item.name" placeholder="{{ __('Logo name') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <input type="text" :name="`{{ $name }}[${index}][items][${i}][image_url]`" x-model="item.image_url" placeholder="{{ __('Logo image URL') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <button type="button" @click="(block.items || []).push({title: '', description: ''})" class="rounded bg-slate-100 px-3 py-1.5 text-xs">{{ __('Add item') }}</button>
                        </div>
                    </template>

                    <template x-if="block.type === 'text'">
                        <div>
                            <textarea :name="`{{ $name }}[${index}][content]`" x-model="block.content" rows="4" placeholder="HTML content" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </template>

                    <template x-if="block.type === 'image'">
                        <div class="grid gap-3 md:grid-cols-2">
                            <input type="text" :name="`{{ $name }}[${index}][src]`" x-model="block.src" placeholder="{{ __('Image URL') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][alt]`" x-model="block.alt" placeholder="{{ __('Alt text') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </template>

                    <template x-if="block.type === 'divider'">
                        <div class="grid gap-3 md:grid-cols-3">
                            <input type="text" :name="`{{ $name }}[${index}][color]`" x-model="block.color" placeholder="{{ __('Color (#e2e8f0)') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][width]`" x-model="block.width" placeholder="{{ __('Width (e.g. 60%, 200px)') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" :name="`{{ $name }}[${index}][icon]`" x-model="block.icon" placeholder="{{ __('Icon image URL') }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </template>

                    <template x-if="block.type === 'spacer'">
                        <div>
                            <select :name="`{{ $name }}[${index}][height]`" x-model="block.height" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="small">{{ __('Small') }}</option>
                                <option value="medium">{{ __('Medium') }}</option>
                                <option value="large">{{ __('Large') }}</option>
                            </select>
                        </div>
                    </template>

                    <details class="rounded-lg border border-slate-100 bg-slate-50">
                        <summary class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Advanced options') }}</summary>
                        <div class="grid gap-3 p-3 md:grid-cols-3">
                            <label class="block text-xs text-slate-600">
                                {{ __('Background color') }}
                                <input type="text" :name="`{{ $name }}[${index}][style][bg]`" x-model="block.style.bg" placeholder="{{ __('#f8fafc') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </label>
                            <label class="block text-xs text-slate-600">
                                {{ __('Text color') }}
                                <input type="text" :name="`{{ $name }}[${index}][style][text_color]`" x-model="block.style.text_color" placeholder="{{ __('#0f172a') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                            <template x-if="['features','stats','testimonials','pricing','steps','logos'].includes(block.type)">
                                <div class="grid gap-3 md:col-span-3 md:grid-cols-3">
                                    <label class="block text-xs text-slate-600">
                                        {{ __('Columns') }}
                                        <select :name="`{{ $name }}[${index}][layout][columns]`" x-model="block.layout.columns" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="0">{{ __('Default') }}</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                        </select>
                                    </label>
                                    <label class="block text-xs text-slate-600">
                                        {{ __('Gap') }}
                                        <select :name="`{{ $name }}[${index}][layout][gap]`" x-model="block.layout.gap" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="small">{{ __('Small') }}</option>
                                            <option value="medium">{{ __('Medium') }}</option>
                                            <option value="large">{{ __('Large') }}</option>
                                        </select>
                                    </label>
                                    <label class="block text-xs text-slate-600">
                                        {{ __('Vertical align') }}
                                        <select :name="`{{ $name }}[${index}][layout][align]`" x-model="block.layout.align" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="stretch">{{ __('Stretch') }}</option>
                                            <option value="start">{{ __('Top') }}</option>
                                            <option value="center">{{ __('Center') }}</option>
                                            <option value="end">{{ __('Bottom') }}</option>
                                        </select>
                                    </label>
                                </div>
                            </template>
                            <label class="flex items-center gap-2 text-xs text-slate-600 md:col-span-3">
                                <input type="hidden" :name="`{{ $name }}[${index}][style][rounded]`" value="0">
                                <input type="checkbox" :name="`{{ $name }}[${index}][style][rounded]`" value="1" x-model="block.style.rounded" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ __('Rounded inner container') }}
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-600 md:col-span-3">
                                <input type="hidden" :name="`{{ $name }}[${index}][style][border]`" value="0">
                                <input type="checkbox" :name="`{{ $name }}[${index}][style][border]`" value="1" x-model="block.style.border" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ __('Add border to cards') }}
                            </label>
                        </div>
                    </details>
                </div>
            </div>
        </template>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <select id="new-block-{{ str_replace(['[',']'], ['-',''], $name) }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($blockTypes as $key => $label)
                <option value="{{ $key }}">{{ __($label) }}</option>
            @endforeach
        </select>
        <button type="button" @click="add(document.getElementById('new-block-{{ str_replace(['[',']'], ['-',''], $name) }}').value)" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">{{ __('Add block') }}</button>
    </div>
</div>
