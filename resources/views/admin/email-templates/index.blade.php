@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.email_templates.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.email_templates.description') }}</p>
        </div>
    </div>

    <div class="mb-4 flex gap-2">
        <a href="{{ route('admin.email-templates.index') }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ is_null($currentTool) ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border border-slate-200' }}">{{ __('All') }}</a>
        @foreach($tools as $key => $label)
            <a href="{{ route('admin.email-templates.index', ['tool' => $key]) }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ $currentTool === $key ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border border-slate-200' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="space-y-6">
        @forelse($grouped as $tool => $templates)
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-slate-50 px-4 py-3 text-sm font-semibold uppercase tracking-wider text-slate-600">
                    {{ $tools[$tool] ?? ucfirst($tool) }}
                </div>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Key') }}</th>
                            <th class="px-4 py-3">{{ __('Locale') }}</th>
                            <th class="px-4 py-3">{{ __('Subject') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($templates as $template)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $template->key }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $template->locale }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ Str::limit($template->subject, 60) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $template->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $template->is_active ? __('Active') : __('Inactive') }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.email-templates.edit', $template) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                                        <button type="button" data-preview-url="{{ route('admin.email-templates.preview', $template) }}" class="preview-btn rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Preview') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-400">
                {{ __('admin.email_templates.empty') }}
            </div>
        @endforelse
    </div>

    <div id="preview-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="max-h-[80vh] w-full max-w-3xl overflow-auto rounded-xl bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">{{ __('Preview') }}</h2>
                <button id="close-preview" class="text-slate-500 hover:text-slate-700">&times;</button>
            </div>
            <div id="preview-content" class="prose max-w-none"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.preview-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const res = await fetch(btn.dataset.previewUrl);
                    const data = await res.json();
                    document.getElementById('preview-content').innerHTML = '<h3>' + (data.subject || '') + '</h3>' + (data.html || '');
                    document.getElementById('preview-modal').classList.remove('hidden');
                    document.getElementById('preview-modal').classList.add('flex');
                });
            });
            document.getElementById('close-preview').addEventListener('click', () => {
                document.getElementById('preview-modal').classList.add('hidden');
                document.getElementById('preview-modal').classList.remove('flex');
            });
        </script>
    @endpush
@endsection
