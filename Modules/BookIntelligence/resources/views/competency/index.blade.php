@php $title = __('Allocore Competency Framework & Career Engine'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" x-data="{ showCreateModal: false }">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0094af]">{{ __('Module 10 & 11 — Competency & Career OS') }}</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ __('Role Competencies & Career Progression Trajectories') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    {{ __('Clear, transparent development ladders connecting company roles, required knowledge, competencies, skills, and recommended books.') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                    + {{ __('Neue Rolle anlegen') }}
                </button>
                <a href="{{ route('bookintelligence.learning.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#ff9200] px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-orange-600 transition">
                    🚀 {{ __('Lernpfad generieren') }} &rarr;
                </a>
            </div>

            <!-- Modal for Adding Role -->
            <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
                <div @click.away="showCreateModal = false" class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-lg font-bold text-slate-900">{{ __('Neue Unternehmensrolle anlegen') }}</h3>
                        <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('bookintelligence.competency.roles.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700">{{ __('Rollenbezeichnung *') }}</label>
                            <input type="text" name="name" required placeholder="z. B. Senior Product Marketing Manager" class="mt-1 w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-[#ff9200] focus:ring-[#ff9200]">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700">{{ __('Abteilung *') }}</label>
                                <input type="text" name="department" required placeholder="Sales, Operations, CS, Tech..." class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700">{{ __('Erfahrungslevel *') }}</label>
                                <select name="level" class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-[#ff9200] focus:ring-[#ff9200]">
                                    <option value="starter">{{ __('Starter') }}</option>
                                    <option value="junior">{{ __('Junior') }}</option>
                                    <option value="professional" selected>{{ __('Professional') }}</option>
                                    <option value="senior">{{ __('Senior') }}</option>
                                    <option value="expert">{{ __('Expert') }}</option>
                                    <option value="master">{{ __('Master') }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700">{{ __('Kurzbeschreibung & Kernverantwortung') }}</label>
                            <textarea name="description" rows="2" placeholder="Was ist das Hauptziel und die Verantwortung dieser Rolle?" class="mt-1 w-full rounded-xl border-slate-300 text-xs"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700">{{ __('Erforderliches Wissen (Kommagetrennt oder Zeilenumbruch)') }}</label>
                            <textarea name="required_knowledge" rows="2" placeholder="z. B. MEDDPICC, Unit Economics, SaaS Metriken..." class="mt-1 w-full rounded-xl border-slate-300 text-xs"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700">{{ __('Kern-Kompetenzen') }}</label>
                                <textarea name="required_competencies" rows="2" placeholder="Pipeline Generation, Closing..." class="mt-1 w-full rounded-xl border-slate-300 text-xs"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700">{{ __('Taktische Skills') }}</label>
                                <textarea name="required_skills" rows="2" placeholder="Cold Calling, CRM Hygiene..." class="mt-1 w-full rounded-xl border-slate-300 text-xs"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                            <button type="button" @click="showCreateModal = false" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                {{ __('Abbrechen') }}
                            </button>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-[#ff9200] to-orange-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:from-orange-600 hover:to-orange-700">
                                💾 {{ __('Rolle speichern') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Career Progression Paths Visual Map -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span>🪜</span> {{ __('Career Progression Paths') }}
            </h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach ($careerPaths as $path)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 space-y-3">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="rounded bg-blue-100 px-2 py-0.5 text-blue-800">{{ $path->fromRole->name }}</span>
                            <span class="text-slate-400">&rarr;</span>
                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-emerald-800">{{ $path->toRole->name }}</span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">{{ $path->title }}</h3>
                        <p class="text-xs text-slate-600">{{ $path->description }}</p>
                        @if (!empty($path->milestones))
                            <div class="space-y-1 text-xs text-slate-700 pt-2 border-t border-slate-200">
                                <span class="font-bold text-slate-900">{{ __('Key Milestones:') }}</span>
                                <ul class="list-disc pl-4 space-y-0.5">
                                    @foreach ($path->milestones as $m)
                                        <li>{{ $m }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Company Roles Matrix -->
        <h2 class="text-xl font-bold text-slate-900 pt-2">{{ __('Role Competency Matrices') }}</h2>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($roles as $role)
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="rounded-md bg-blue-50 px-2.5 py-0.5 text-[11px] font-bold uppercase text-blue-700">{{ $role->department }}</span>
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">{{ $role->level }}</span>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ $role->name }}</h3>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $role->description }}</p>
                        </div>

                        <!-- Competencies Badges -->
                        @if (!empty($role->required_competencies))
                            <div class="space-y-1.5">
                                <span class="text-[11px] font-bold uppercase text-slate-400">{{ __('Core Competencies:') }}</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($role->required_competencies as $comp)
                                        <span class="rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-800">{{ $comp }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Required Skills -->
                        @if (!empty($role->required_skills))
                            <div class="space-y-1.5">
                                <span class="text-[11px] font-bold uppercase text-slate-400">{{ __('Required Skills:') }}</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($role->required_skills as $skill)
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4 flex items-center justify-between text-xs">
                        <a href="{{ route('bookintelligence.competency.roles.show', $role->id) }}" class="font-bold text-[#0094af] hover:underline">
                            {{ __('Explore Full Role Framework') }} &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
