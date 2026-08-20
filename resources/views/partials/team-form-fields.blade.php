@php
$team = $team ?? null;
$selectedIndustry = $selected['industry'] ?? old('industry', $team?->industry ?? '');
$selectedSub = $selected['industry_sub'] ?? old('industry_sub', $team?->industry_sub ?? '');
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Team / Company name') }}</label>
        <input type="text" name="name" value="{{ old('name', $team?->name) }}" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Company name') }}</label>
        <input type="text" name="company_name" value="{{ old('company_name', $team?->company_name) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]" placeholder="{{ __('Optional public company name') }}">
    </div>

    @include('partials.industry-select', ['clusters' => $clusters, 'value' => $team, 'selected' => ['industry' => $selectedIndustry, 'industry_sub' => $selectedSub]])

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Company size') }}</label>
            <select name="size" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                <option value="">{{ __('Select size') }}</option>
                @foreach (['1–10', '11–50', '51–200', '201–500', '501+'] as $size)
                    <option value="{{ $size }}" {{ old('size', $team?->size) === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Company age (years)') }}</label>
            <input type="number" name="company_age" min="0" max="250" value="{{ old('company_age', $team?->company_age) }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Country') }}</label>
            <select name="country" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                <option value="">{{ __('Select country') }}</option>
                @foreach ([
                    'Germany', 'Austria', 'Switzerland', 'Netherlands', 'Belgium', 'France',
                    'Italy', 'Spain', 'Poland', 'Czech Republic', 'United Kingdom', 'United States',
                    'Canada', 'Other'
                ] as $country)
                    <option value="{{ $country }}" {{ old('country', $team?->country) === $country ? 'selected' : '' }}>{{ $country }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Revenue range') }}</label>
            <select name="revenue_range" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                <option value="">{{ __('Select revenue') }}</option>
                @foreach ([
                    '< €500k', '€500k – €1M', '€1M – €5M', '€5M – €25M', '€25M – €100M', '> €100M'
                ] as $range)
                    <option value="{{ $range }}" {{ old('revenue_range', $team?->revenue_range) === $range ? 'selected' : '' }}>{{ $range }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
