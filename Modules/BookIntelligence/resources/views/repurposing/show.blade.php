@php $title = __('Content Assets: :book', ['book' => $book->title]); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="space-y-6" data-no-navigate x-data="{ activeTab: 'blogs' }">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Book Header Banner -->
        <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                @if ($book->cover_url)
                    <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="h-24 w-16 rounded-xl object-cover shadow-md">
                @else
                    <div class="flex h-24 w-16 items-center justify-center rounded-xl bg-amber-100 text-3xl">📖</div>
                @endif
                <div>
                    <a href="{{ route('bookintelligence.repurposing.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700">
                        &larr; {{ __('All Repurposed Books') }}
                    </a>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $book->title }}</h1>
                    <p class="text-xs text-slate-500">{{ __('By') }} {{ $book->author?->name ?? 'Author' }} • {{ $book->mainTopic?->name }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('bookintelligence.repurposing.generate', $book->id) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#ff9200] px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-orange-600">
                    🔄 {{ __('Regenerate Full Content Asset Bundle') }}
                </button>
            </form>
        </div>

        @if (! $bundle)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-2xl text-amber-600">
                    ⚡
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900">{{ __('No Repurposed Content Bundle Yet') }}</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    {{ __('Click Generate below to produce 20 Blogs, 50 LinkedIn posts, 20 FAQs, 10 Checklists, 10 Guides, and 5 Whitepapers.') }}
                </p>
                <form method="POST" action="{{ route('bookintelligence.repurposing.generate', $book->id) }}" class="mt-6">
                    @csrf
                    <button type="submit" class="rounded-xl bg-[#ff9200] px-6 py-3 text-sm font-bold text-white shadow-md hover:bg-orange-600">
                        ⚡ {{ __('Generate Asset Bundle') }}
                    </button>
                </form>
            </div>
        @else
            <!-- Asset Category Navigation Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
                <button @click="activeTab = 'blogs'" :class="activeTab === 'blogs' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'" class="rounded-xl px-4 py-2 text-xs font-bold transition">
                    📝 {{ __('20 Blog Articles') }} ({{ count($bundle->blog_articles ?? []) }})
                </button>
                <button @click="activeTab = 'linkedin'" :class="activeTab === 'linkedin' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100'" class="rounded-xl px-4 py-2 text-xs font-bold transition">
                    💼 {{ __('50 LinkedIn Posts') }} ({{ count($bundle->linkedin_posts ?? []) }})
                </button>
                <button @click="activeTab = 'faqs'" :class="activeTab === 'faqs' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-100'" class="rounded-xl px-4 py-2 text-xs font-bold transition">
                    ❓ {{ __('20 FAQs') }} ({{ count($bundle->faq_articles ?? []) }})
                </button>
                <button @click="activeTab = 'checklists'" :class="activeTab === 'checklists' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100'" class="rounded-xl px-4 py-2 text-xs font-bold transition">
                    ✅ {{ __('10 Checklists') }} ({{ count($bundle->checklists ?? []) }})
                </button>
                <button @click="activeTab = 'guides'" :class="activeTab === 'guides' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100'" class="rounded-xl px-4 py-2 text-xs font-bold transition">
                    📘 {{ __('10 Practical Guides') }} ({{ count($bundle->practical_guides ?? []) }})
                </button>
                <button @click="activeTab = 'whitepapers'" :class="activeTab === 'whitepapers' ? 'bg-slate-700 text-white' : 'text-slate-600 hover:bg-slate-100'" class="rounded-xl px-4 py-2 text-xs font-bold transition">
                    📄 {{ __('5 Whitepapers') }} ({{ count($bundle->whitepaper_concepts ?? []) }})
                </button>
            </div>

            <!-- Tab 1: Blog Articles -->
            <div x-show="activeTab === 'blogs'" class="grid gap-4 md:grid-cols-2">
                @foreach ($bundle->blog_articles ?? [] as $idx => $item)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">#{{ $idx + 1 }}</span>
                            <span class="text-xs text-slate-400">🔑 {{ $item['keyword'] ?? 'SEO Keyword' }}</span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 leading-snug">{{ $item['title'] }}</h3>
                        <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg"><strong>Angle:</strong> {{ $item['angle'] ?? '' }}</p>
                        <form method="POST" action="{{ route('bookintelligence.content.blog.generate') }}">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <input type="hidden" name="custom_topic" value="{{ $item['title'] }}">
                            <button type="submit" class="w-full rounded-xl bg-slate-900 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                ⚡ {{ __('Write Full 8-Section Article') }} &rarr;
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <!-- Tab 2: LinkedIn Posts -->
            <div x-show="activeTab === 'linkedin'" class="grid gap-4 md:grid-cols-2">
                @foreach ($bundle->linkedin_posts ?? [] as $idx => $post)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800">Post #{{ $idx + 1 }} ({{ $post['type'] ?? 'Story' }})</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900">🪝 {{ $post['hook'] ?? '' }}</h4>
                        <div class="rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-700 whitespace-pre-line">
                            {{ $post['content'] ?? '' }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tab 3: FAQs -->
            <div x-show="activeTab === 'faqs'" class="space-y-3">
                @foreach ($bundle->faq_articles ?? [] as $idx => $faq)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-2">
                        <h4 class="text-sm font-bold text-slate-900">❓ {{ $faq['question'] }}</h4>
                        <p class="text-xs text-slate-700 leading-relaxed">{{ $faq['answer'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Tab 4: Checklists -->
            <div x-show="activeTab === 'checklists'" class="grid gap-4 md:grid-cols-2">
                @foreach ($bundle->checklists ?? [] as $idx => $chk)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                        <h4 class="text-sm font-bold text-slate-900">✅ {{ $chk['title'] }}</h4>
                        <ul class="space-y-1.5 text-xs text-slate-700">
                            @foreach ($chk['items'] ?? [] as $item)
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <!-- Tab 5: Practical Guides -->
            <div x-show="activeTab === 'guides'" class="grid gap-4 md:grid-cols-2">
                @foreach ($bundle->practical_guides ?? [] as $idx => $guide)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                        <h4 class="text-sm font-bold text-slate-900">📘 {{ $guide['title'] }}</h4>
                        <ol class="list-decimal pl-4 space-y-1 text-xs text-slate-700">
                            @foreach ($guide['steps'] ?? [] as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                        @if (!empty($guide['outcome']))
                            <p class="text-[11px] text-emerald-800 bg-emerald-50 rounded p-2">
                                🎯 <strong>Outcome:</strong> {{ $guide['outcome'] }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Tab 6: Whitepapers -->
            <div x-show="activeTab === 'whitepapers'" class="space-y-4">
                @foreach ($bundle->whitepaper_concepts ?? [] as $idx => $wp)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded bg-slate-900 px-2.5 py-0.5 text-xs font-bold text-white">Whitepaper #{{ $idx + 1 }}</span>
                            <span class="text-xs text-slate-500">🎯 {{ $wp['target_audience'] ?? 'Executive Leadership' }}</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">{{ $wp['title'] }}</h3>
                        <p class="text-xs leading-relaxed text-slate-700">{{ $wp['summary'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
