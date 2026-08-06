@extends('layouts.shell')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Integrations') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Add Integration') }}</h2>
        <form method="POST" action="{{ route('devmanager.integrations.store') }}" class="grid gap-4 md:grid-cols-5">
            @csrf
            <div class="md:col-span-2">
                <select name="provider" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    <option value="github">GitHub</option>
                    <option value="azure_devops">Azure DevOps</option>
                    <option value="jira">Jira</option>
                    <option value="clickup">ClickUp</option>
                </select>
            </div>
            <div>
                <input type="url" name="config[url]" placeholder="{{ __('URL') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
            </div>
            <div>
                <input type="text" name="config[project]" placeholder="{{ __('Project / Board') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
            </div>
            <div>
                <input type="text" name="config[token]" placeholder="{{ __('Token') }}" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
            </div>
            <div class="md:col-span-5 flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ __('Save') }}</button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($integrations->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No integrations configured.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-slate-100 text-left text-xs uppercase text-slate-500"><tr><th class="pb-3 pr-4">{{ __('Provider') }}</th><th class="pb-3 pr-4">{{ __('Project') }}</th><th class="pb-3 pr-4">{{ __('URL') }}</th><th class="pb-3 pr-4 text-right">{{ __('Actions') }}</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($integrations as $integration)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-900">{{ $integration->provider }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $integration->config['project'] ?? '—' }}</td>
                                <td class="py-3 pr-4 text-slate-600"><a href="{{ $integration->config['url'] ?? '#' }}" target="_blank" class="text-[#0094af] hover:underline">{{ $integration->config['url'] ?? '—' }}</a></td>
                                <td class="py-3 pr-4 text-right">
                                    <form method="POST" action="{{ route('devmanager.integrations.destroy', $integration) }}" onsubmit="return confirm('{{ __("Remove integration?") }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-rose-600 hover:underline">{{ __('Remove') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $integrations->links() }}</div>
        @endif
    </div>
</div>
@endsection
