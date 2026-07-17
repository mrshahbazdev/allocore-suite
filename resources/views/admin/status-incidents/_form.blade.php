<form method="POST" action="{{ $action }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
            <input type="text" name="title" value="{{ old('title', $incident->title ?? '') }}" required class="mt-2 w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
            <textarea name="description" rows="4" class="mt-2 w-full rounded-lg border-slate-300 px-3 py-2 text-sm">{{ old('description', $incident->description ?? '') }}</textarea>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Severity') }}</label>
                <select name="severity" class="mt-2 w-full rounded-lg border-slate-300 text-sm">
                    @foreach (['critical', 'major', 'minor', 'maintenance'] as $severity)
                        <option value="{{ $severity }}" {{ old('severity', $incident->severity ?? 'minor') === $severity ? 'selected' : '' }}>{{ ucfirst($severity) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="status" class="mt-2 w-full rounded-lg border-slate-300 text-sm">
                    @foreach (['investigating', 'identified', 'monitoring', 'resolved'] as $status)
                        <option value="{{ $status }}" {{ old('status', $incident->status ?? 'investigating') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Started at') }}</label>
            <input type="datetime-local" name="started_at" value="{{ old('started_at', isset($incident) ? $incident->started_at->format('Y-m-d\\TH:i') : now()->format('Y-m-d\\TH:i')) }}" required class="mt-2 w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
        </div>
    </div>

    <div class="mt-8 flex justify-end">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ $button }}</button>
    </div>
</form>
