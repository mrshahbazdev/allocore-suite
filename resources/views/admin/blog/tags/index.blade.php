@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Blog Tags') }}</h1>
        </div>
        <a href="{{ route('admin.blog.tags.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New tag') }}</a>
    </div>

    @if ($tags->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No tags yet.') }}</div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Slug') }}</th>
                        <th class="px-5 py-3 font-medium text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($tags as $tag)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $tag->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $tag->slug }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.blog.tags.edit', $tag) }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.blog.tags.destroy', $tag) }}" class="ml-3 inline" onsubmit="return confirm('{{ __('Delete this tag?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
