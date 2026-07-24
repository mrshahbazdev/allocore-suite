<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
        <input type="text" name="title" value="{{ old('title', $report?->title) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Report type') }}</label>
        <select name="report_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            <option value="dashboard" {{ old('report_type', $report?->report_type) === 'dashboard' ? 'selected' : '' }}>{{ __('Dashboard summary') }}</option>
            <option value="module_summary" {{ old('report_type', $report?->report_type) === 'module_summary' ? 'selected' : '' }}>{{ __('Module summary') }}</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Module') }}</label>
        <select name="module_key" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            <option value="">{{ __('All / Dashboard') }}</option>
            @foreach ($modules as $module)
                <option value="{{ $module->key }}" {{ old('module_key', $report?->module_key) === $module->key ? 'selected' : '' }}>{{ $module->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Frequency') }}</label>
        <select name="frequency" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            <option value="daily" {{ old('frequency', $report?->frequency) === 'daily' ? 'selected' : '' }}>{{ __('Daily') }}</option>
            <option value="weekly" {{ old('frequency', $report?->frequency) === 'weekly' ? 'selected' : '' }}>{{ __('Weekly') }}</option>
            <option value="monthly" {{ old('frequency', $report?->frequency) === 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Format') }}</label>
        <select name="format" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            <option value="pdf" {{ old('format', $report?->format) === 'pdf' ? 'selected' : '' }}>{{ __('PDF') }}</option>
            <option value="csv" {{ old('format', $report?->format) === 'csv' ? 'selected' : '' }}>{{ __('CSV') }}</option>
        </select>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
        <input type="email" name="email" value="{{ old('email', $report?->email) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
    </div>

    <div class="flex items-center gap-2 sm:col-span-2">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $report?->is_active ?? true) ? 'checked' : '' }} id="is_active" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        <label for="is_active" class="text-sm text-slate-700">{{ __('Active') }}</label>
    </div>
</div>
