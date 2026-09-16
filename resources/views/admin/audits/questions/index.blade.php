@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Audit-Fragen & Lösungszuweisung') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Durchsuchen Sie alle Fragen und weisen Sie Tools, Bücher, Blogartikel und Glossarbegriffe zu.') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.audits.templates.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Vorlagen') }}</a>
            <a href="{{ route('admin.audits.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Audits') }}</a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.audits.questions.index') }}" class="grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Frage oder Stichwort suchen') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('z.B. Umsatz, Mitarbeiter, Prozesse...') }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Säule filtern') }}</label>
                <select name="pillar_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('— Alle Säulen —') }}</option>
                    @foreach ($pillars as $p)
                        <option value="{{ $p->id }}" @selected(request('pillar_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">{{ __('Vorlage filtern') }}</label>
                <select name="template_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('— Alle Vorlagen —') }}</option>
                    @foreach ($templates as $t)
                        <option value="{{ $t->id }}" @selected(request('template_id') == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4 flex items-center justify-end gap-2 pt-1">
                @if (request()->hasAny(['search', 'pillar_id', 'template_id']))
                    <a href="{{ route('admin.audits.questions.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Filter zurücksetzen') }}</a>
                @endif
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">{{ __('Fragen filtern') }}</button>
            </div>
        </form>
    </div>

    <!-- Questions Table -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-600">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-600">{{ __('Frage & Details') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-600">{{ __('Säule / Vorlage') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-600">{{ __('Zugewiesene Lösungen') }}</th>
                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-600">{{ __('Aktion') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($questions as $question)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-4 text-xs font-mono font-semibold text-slate-400 align-top">
                                #{{ $question->id }}
                            </td>
                            <td class="px-5 py-4 align-top max-w-md">
                                <div class="font-semibold text-slate-900 text-sm">{{ $question->question }}</div>
                                @if ($question->description)
                                    <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $question->description }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 font-medium">{{ $question->question_type }}</span>
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5">Gewicht: {{ $question->weight }}</span>
                                    @if ($question->is_required)
                                        <span class="rounded-md bg-amber-50 px-2 py-0.5 text-amber-700 font-medium">Pflicht</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top text-xs">
                                <div class="font-semibold text-indigo-900">{{ $question->pillar?->name ?? '—' }}</div>
                                <div class="text-slate-400 mt-0.5">{{ $question->template?->name ?? 'Vorlage' }}</div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="space-y-1.5 text-xs">
                                    @if ($question->recommended_module_key)
                                        <div class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-indigo-700 font-medium">
                                            <span>🛠️ Tool:</span>
                                            <span class="font-bold">{{ $question->recommended_module_key }}</span>
                                        </div>
                                    @endif

                                    @if ($question->recommended_book_id && $question->recommendedBook)
                                        <div class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-amber-800 font-medium">
                                            <span>📚 Buch:</span>
                                            <span class="font-bold truncate max-w-[180px]">{{ $question->recommendedBook->title }}</span>
                                        </div>
                                    @endif

                                    @if ($question->recommended_post_id && $question->recommendedPost)
                                        <div class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1 text-blue-800 font-medium">
                                            <span>📰 Artikel:</span>
                                            <span class="font-bold truncate max-w-[180px]">{{ $question->recommendedPost->title }}</span>
                                        </div>
                                    @endif

                                    @if ($question->knowledge_slug)
                                        <div class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 text-emerald-800 font-medium">
                                            <span>💡 Glossar:</span>
                                            <span class="font-bold">{{ $question->knowledge_slug }}</span>
                                        </div>
                                    @endif

                                    @if (! $question->recommended_module_key && ! $question->recommended_book_id && ! $question->recommended_post_id && ! $question->knowledge_slug)
                                        <span class="inline-block text-slate-400 italic">{{ __('Keine direkte Lösung hinterlegt') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right align-top">
                                <a href="{{ route('admin.audits.questions.edit', $question) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    {{ __('Lösung zuweisen') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                {{ __('Keine Fragen gefunden.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($questions->hasPages())
            <div class="border-t border-slate-200 p-4">
                {{ $questions->links() }}
            </div>
        @endif
    </div>
@endsection
