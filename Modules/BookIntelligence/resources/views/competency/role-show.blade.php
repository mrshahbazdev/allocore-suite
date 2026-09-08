@php $title = $role->name; @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-5xl space-y-6" data-no-navigate x-data="{ showEditModal: false }">
        <div class="flex items-center justify-between">
            <a href="{{ route('bookintelligence.competency.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; {{ __('Back to Competency Framework') }}
            </a>
            <div class="flex items-center gap-2">
                <button type="button" @click="showEditModal = true" class="rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                    ✏️ {{ __('Rolle bearbeiten') }}
                </button>
                <form method="POST" action="{{ route('bookintelligence.competency.roles.destroy', $role->id) }}" onsubmit="return confirm('Möchten Sie diese Rolle wirklich löschen?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-200 bg-white px-3.5 py-1.5 text-xs font-bold text-red-600 shadow-xs hover:bg-red-50 transition">
                        🗑️ {{ __('Löschen') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Role Header Card -->
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-md bg-blue-50 px-2.5 py-0.5 text-xs font-bold uppercase text-blue-700">{{ $role->department }}</span>
                    <span class="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-bold uppercase text-slate-700">{{ $role->level }} Level</span>
                </div>
                <a href="{{ route('bookintelligence.learning.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#ff9200] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-orange-600 transition">
                    🚀 {{ __('Lernpfad für diese Rolle erstellen') }} &rarr;
                </a>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900">{{ $role->name }}</h1>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $role->description }}</p>

            @if ($role->nextRole)
                <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 text-xs">
                    <span class="font-bold text-amber-900">{{ __('Next Career Progression Level:') }}</span>
                    <strong class="text-slate-900 font-bold ml-1">{{ $role->nextRole->name }}</strong>
                    <span class="text-slate-500">({{ $role->nextRole->level }})</span>
                </div>
            @endif
        </div>

        <!-- 3 Pillars of Competency -->
        <div class="grid gap-6 md:grid-cols-3">
            <!-- Pillar 1: Required Knowledge -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>📚</span> {{ __('Required Knowledge') }}
                </h3>
                <ul class="space-y-2 text-xs text-slate-700">
                    @forelse ($role->required_knowledge ?? [] as $k)
                        <li class="rounded-lg bg-slate-50 p-2.5 font-medium border border-slate-100">{{ $k }}</li>
                    @empty
                        <li class="text-slate-400">{{ __('None listed.') }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- Pillar 2: Core Competencies -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>🎯</span> {{ __('Core Competencies') }}
                </h3>
                <ul class="space-y-2 text-xs text-slate-700">
                    @forelse ($role->required_competencies ?? [] as $c)
                        <li class="rounded-lg bg-emerald-50 p-2.5 font-medium border border-emerald-100 text-emerald-900">{{ $c }}</li>
                    @empty
                        <li class="text-slate-400">{{ __('None listed.') }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- Pillar 3: Tactical Skills -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>🛠️</span> {{ __('Tactical Skills') }}
                </h3>
                <ul class="space-y-2 text-xs text-slate-700">
                    @forelse ($role->required_skills ?? [] as $s)
                        <li class="rounded-lg bg-blue-50 p-2.5 font-medium border border-blue-100 text-blue-900">{{ $s }}</li>
                    @empty
                        <li class="text-slate-400">{{ __('None listed.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
            <div @click.away="showEditModal = false" class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Rolle bearbeiten') }}</h3>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('bookintelligence.competency.roles.update', $role->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Rollenbezeichnung *') }}</label>
                        <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700">{{ __('Abteilung *') }}</label>
                            <input type="text" name="department" value="{{ old('department', $role->department) }}" required class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700">{{ __('Erfahrungslevel *') }}</label>
                            <select name="level" class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                                @foreach (['starter' => 'Starter', 'junior' => 'Junior', 'professional' => 'Professional', 'senior' => 'Senior', 'expert' => 'Expert', 'master' => 'Master'] as $lvlKey => $lvlLabel)
                                    <option value="{{ $lvlKey }}" @selected(strtolower($role->level) === $lvlKey)>{{ $lvlLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Nächste Karrierestufe (Optional)') }}</label>
                        <select name="next_role_id" class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                            <option value="">-- {{ __('Keine ausgewählt') }} --</option>
                            @foreach ($allRoles as $r)
                                <option value="{{ $r->id }}" @selected($role->next_role_id === $r->id)>{{ $r->name }} ({{ $r->level }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Kurzbeschreibung & Kernverantwortung') }}</label>
                        <textarea name="description" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-xs">{{ old('description', $role->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">{{ __('Erforderliches Wissen (Kommagetrennt oder Zeilenumbruch)') }}</label>
                        <textarea name="required_knowledge" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-xs">{{ implode(', ', $role->required_knowledge ?? []) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700">{{ __('Kern-Kompetenzen') }}</label>
                            <textarea name="required_competencies" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-xs">{{ implode(', ', $role->required_competencies ?? []) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700">{{ __('Taktische Skills') }}</label>
                            <textarea name="required_skills" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-xs">{{ implode(', ', $role->required_skills ?? []) }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            {{ __('Abbrechen') }}
                        </button>
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:from-orange-600 hover:to-orange-700">
                            💾 {{ __('Änderungen speichern') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
