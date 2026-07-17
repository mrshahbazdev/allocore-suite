@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Users') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.users.description') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to admin') }}</a>
            <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.users.create_button') }}</a>
        </div>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between">
            <form id="bulk-form" method="POST" action="{{ route('admin.users.bulk') }}" class="flex flex-wrap items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="action" class="rounded-lg border-slate-300 text-sm">
                    <option value="">{{ __('admin.bulk_users.action') }}</option>
                    <option value="activate">{{ __('admin.bulk_users.activate') }}</option>
                    <option value="deactivate">{{ __('admin.bulk_users.deactivate') }}</option>
                    <option value="verify">{{ __('admin.bulk_users.verify_email') }}</option>
                    <option value="delete">{{ __('admin.bulk_users.delete') }}</option>
                </select>
                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500" onclick="return prepareBulkForm()">{{ __('admin.bulk_users.apply') }}</button>
            </form>

            <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.users.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
            </form>
        </div>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3"><input type="checkbox" id="select-all" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"></th>
                    <th class="px-4 py-3">{{ __('User') }}</th>
                    <th class="px-4 py-3">{{ __('Team') }}</th>
                    <th class="px-4 py-3">{{ __('Roles') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Subscriptions') }}</th>
                    <th class="px-4 py-3">{{ __('Verified') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3"><input type="checkbox" class="user-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" value="{{ $user->id }}"></td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->currentTeam?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->roles->pluck('name')->join(', ') ?: 'user' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $user->is_active ? __('Active') : __('Inactive') }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->toolSubscriptions->count() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->hasVerifiedEmail() ? __('Yes') : __('No') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.subscriptions.index', $user) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('admin.users.subscriptions') }}</a>
                                <a href="{{ route('admin.users.impersonate', $user) }}" class="rounded-lg border border-indigo-300 px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">{{ __('admin.users.impersonate') }}</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-400">{{ __('No users found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

    <script>
        document.getElementById('select-all')?.addEventListener('change', function (e) {
            document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = e.target.checked);
        });

        function prepareBulkForm() {
            const form = document.getElementById('bulk-form');
            document.querySelectorAll('.bulk-selected-input').forEach(el => el.remove());

            const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
            if (selected.length === 0) {
                alert('{{ __('admin.bulk_users.select_warning') }}');
                return false;
            }

            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected[]';
                input.value = id;
                input.classList.add('bulk-selected-input');
                form.appendChild(input);
            });

            const action = form.querySelector('[name="action"]').value;
            if (action === 'delete' && ! confirm('{{ __('admin.bulk_users.confirm_delete') }}')) {
                return false;
            }

            return true;
        }
    </script>
@endsection
