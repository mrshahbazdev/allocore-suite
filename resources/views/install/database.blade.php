@extends('install.layout')

@section('content')
    <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Database Configuration') }}</h2>

    <form method="POST" action="{{ route('install.database.store') }}">
        @csrf

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Database driver') }}</label>
            <select id="db_driver" name="db_driver" onchange="toggleDriver(this.value)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                <option value="sqlite" {{ old('db_driver', 'sqlite') === 'sqlite' ? 'selected' : '' }}>SQLite</option>
                <option value="mysql" {{ old('db_driver') === 'mysql' ? 'selected' : '' }}>MySQL</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Database file / name') }}</label>
            <input type="text" name="db_database" value="{{ old('db_database', 'database.sqlite') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            <p class="mt-1 text-xs text-slate-500" id="db_hint">{{ __('Relative to the database/ directory, or an absolute path.') }}</p>
        </div>

        <div id="mysql-fields" class="space-y-4" style="display:none">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Host') }}</label>
                    <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" class="mysql-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Port') }}</label>
                    <input type="number" name="db_port" value="{{ old('db_port', 3306) }}" class="mysql-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Username') }}</label>
                <input type="text" name="db_username" value="{{ old('db_username', 'root') }}" class="mysql-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Password') }}</label>
                <input type="password" name="db_password" value="{{ old('db_password') }}" class="mysql-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
        </div>

        <div class="mb-6 mt-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Table prefix') }}</label>
            <input type="text" name="db_prefix" value="{{ old('db_prefix') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
            {{ __('Next: Admin Account') }}
        </button>
    </form>

    <script>
        function toggleDriver(driver) {
            const mysqlFields = document.getElementById('mysql-fields');
            const hint = document.getElementById('db_hint');
            const inputs = document.querySelectorAll('.mysql-input');

            if (driver === 'mysql') {
                mysqlFields.style.display = 'block';
                hint.textContent = '{{ __("Name of the MySQL database.") }}';
                inputs.forEach(input => input.disabled = false);
            } else {
                mysqlFields.style.display = 'none';
                hint.textContent = '{{ __("Relative to the database/ directory, or an absolute path.") }}';
                inputs.forEach(input => input.disabled = true);
            }
        }

        toggleDriver(document.getElementById('db_driver').value);
    </script>
@endsection
