@props(['name', 'value' => ''])
<div x-data="{ html: @js($value) }" x-init="$refs.editor.innerHTML = html" class="rounded-xl border border-slate-300 bg-white overflow-hidden">
    <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 px-3 py-2">
        <button type="button" @click="document.execCommand('bold', false, null)" class="rounded px-2 py-1 text-xs font-bold text-slate-700 hover:bg-slate-200" title="Bold">B</button>
        <button type="button" @click="document.execCommand('italic', false, null)" class="rounded px-2 py-1 text-xs italic text-slate-700 hover:bg-slate-200" title="Italic">I</button>
        <button type="button" @click="document.execCommand('underline', false, null)" class="rounded px-2 py-1 text-xs underline text-slate-700 hover:bg-slate-200" title="Underline">U</button>
        <button type="button" @click="document.execCommand('formatBlock', false, 'H2')" class="rounded px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200" title="Heading">H2</button>
        <button type="button" @click="document.execCommand('formatBlock', false, 'P')" class="rounded px-2 py-1 text-xs text-slate-700 hover:bg-slate-200" title="Paragraph">P</button>
        <button type="button" @click="document.execCommand('insertUnorderedList', false, null)" class="rounded px-2 py-1 text-xs text-slate-700 hover:bg-slate-200" title="Bullet list">•</button>
        <button type="button" @click="document.execCommand('insertOrderedList', false, null)" class="rounded px-2 py-1 text-xs text-slate-700 hover:bg-slate-200" title="Numbered list">1.</button>
        <button type="button" @click="const url = prompt('URL'); if (url) document.execCommand('createLink', false, url)" class="rounded px-2 py-1 text-xs text-indigo-700 hover:bg-indigo-100" title="Link">Link</button>
        <button type="button" @click="document.execCommand('removeFormat', false, null)" class="rounded px-2 py-1 text-xs text-rose-700 hover:bg-rose-100" title="Clear formatting">Clear</button>
    </div>
    <div x-ref="editor" contenteditable="true" @input="html = $refs.editor.innerHTML" class="min-h-[12rem] w-full p-3 text-sm text-slate-700 focus:outline-none"></div>
    <textarea x-ref="textarea" name="{{ $name }}" x-model="html" class="hidden"></textarea>
</div>
