@php $title = __('Mass Import Books (Excel / CSV)'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('bookintelligence.books.index') }}" class="text-xs font-semibold text-[#0094af] hover:underline">&larr; {{ __('Back to Library') }}</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ __('Mass Import Books (Excel / CSV)') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Upload your Excel (.xlsx, .xls) or CSV catalog to import hundreds of books, categories, and reading statuses at once.') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('bookintelligence.books.import.template') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    {{ __('Download CSV Template') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Upload Form --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <form action="{{ route('bookintelligence.books.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-slate-900">{{ __('Upload File (.xlsx, .xls, .csv, .txt)') }}</label>
                        <p class="mt-1 text-xs text-slate-500">{{ __('Max file size: 20MB. Supports Excel and CSV files with comma, semicolon, or tab separators.') }}</p>

                        <div class="mt-3 flex justify-center rounded-2xl border-2 border-dashed border-slate-300 px-6 py-10 hover:border-[#ff9200] transition">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="mt-4 flex text-sm leading-6 text-slate-600 justify-center">
                                    <label for="file" class="relative cursor-pointer rounded-md font-semibold text-[#0094af] focus-within:outline-none hover:underline">
                                        <span>{{ __('Select your Excel / CSV file') }}</span>
                                        <input id="file" name="file" type="file" accept=".csv,.xlsx,.xls,.txt" required class="sr-only">
                                    </label>
                                </div>
                                <p id="file-chosen" class="mt-1 text-xs text-slate-500">{{ __('No file selected yet') }}</p>
                            </div>
                        </div>
                        @error('file')
                            <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900">{{ __('Duplicate Handling') }}</label>
                        <div class="mt-2 space-y-2">
                            <label class="flex items-center gap-2 text-xs text-slate-700">
                                <input type="radio" name="duplicate_handling" value="skip" checked class="text-[#ff9200] focus:ring-[#ff9200]">
                                <span><strong>{{ __('Skip duplicates') }}</strong> — {{ __('Do not overwrite existing books with matching Title or ISBN.') }}</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-700">
                                <input type="radio" name="duplicate_handling" value="update" class="text-[#ff9200] focus:ring-[#ff9200]">
                                <span><strong>{{ __('Update existing') }}</strong> — {{ __('Update matching books with new categories, reading progress, notes, or descriptions.') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500">{{ __('All categories & authors in the file will be automatically registered.') }}</span>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#ff9200] px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-orange-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            {{ __('Start Mass Import') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- Supported Columns Reference & Stats --}}
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">{{ __('Supported Columns') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Columns can be in English or German and in any order:') }}</p>

                    <div class="mt-3 space-y-2 text-xs">
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="font-semibold text-slate-800">Title / Titel</span>
                            <span class="text-emerald-600 font-bold">{{ __('Required') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">Author / Autor</span>
                            <span class="text-slate-400">{{ __('Auto-created') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">Category / Topic / Kategorie</span>
                            <span class="text-slate-400">{{ __('Auto-created') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">Reading Status (read, reading, planned)</span>
                            <span class="text-slate-400">{{ __('Auto-synced') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">Reading Notes / Notizen</span>
                            <span class="text-slate-400">{{ __('Optional') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">Publisher / Verlag</span>
                            <span class="text-slate-400">{{ __('Optional') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">ISBN</span>
                            <span class="text-slate-400">{{ __('Optional') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">Publication Year / Jahr</span>
                            <span class="text-slate-400">{{ __('Optional') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">Pages / Seitenzahl</span>
                            <span class="text-slate-400">{{ __('Optional') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-1.5">
                            <span class="text-slate-600">Difficulty (beginner, intermediate, advanced)</span>
                            <span class="text-slate-400">{{ __('Optional') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Amazon Link / URL</span>
                            <span class="text-slate-400">{{ __('Optional') }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ __('Current Knowledge Base') }}</h3>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl bg-white p-3 shadow-xs">
                            <div class="text-lg font-bold text-slate-900">{{ $bookCount }}</div>
                            <div class="text-[10px] text-slate-500 uppercase">{{ __('Books') }}</div>
                        </div>
                        <div class="rounded-xl bg-white p-3 shadow-xs">
                            <div class="text-lg font-bold text-slate-900">{{ $authorCount }}</div>
                            <div class="text-[10px] text-slate-500 uppercase">{{ __('Authors') }}</div>
                        </div>
                        <div class="rounded-xl bg-white p-3 shadow-xs">
                            <div class="text-lg font-bold text-slate-900">{{ $topicCount }}</div>
                            <div class="text-[10px] text-slate-500 uppercase">{{ __('Topics') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('file')?.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                document.getElementById('file-chosen').textContent = 'Selected: ' + fileName;
                document.getElementById('file-chosen').classList.add('text-[#0094af]', 'font-semibold');
            }
        });
    </script>
@endsection
