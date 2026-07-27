@php
$selectedIndustry = $selected['industry'] ?? old('industry', $value->industry ?? '');
$selectedSub = $selected['industry_sub'] ?? old('industry_sub', $value->industry_sub ?? '');
@endphp

<div class="grid gap-3 sm:grid-cols-2" data-industry-select>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Industry cluster') }}</label>
        <select name="industry" class="w-full rounded-lg border-slate-300 text-sm" data-industry-cluster>
            <option value="">{{ __('Select cluster') }}</option>
            @foreach ($clusters as $cluster)
                <option value="{{ $cluster->name }}" data-children="{{ $cluster->children->pluck('name')->implode('|') }}" {{ $selectedIndustry === $cluster->name ? 'selected' : '' }}>
                    {{ $cluster->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Sub-industry') }}</label>
        <select name="industry_sub" class="w-full rounded-lg border-slate-300 text-sm" data-industry-sub {{ $selectedIndustry ? '' : 'disabled' }}>
            <option value="">{{ __('Select sub-industry') }}</option>
            @foreach ($clusters as $cluster)
                @foreach ($cluster->children as $sub)
                    <option value="{{ $sub->name }}" data-cluster="{{ $cluster->name }}" {{ $selectedSub === $sub->name ? 'selected' : '' }}>
                        {{ $sub->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
    </div>
</div>

<script>
    document.addEventListener('change', function (e) {
        if (! e.target.matches('[data-industry-cluster]')) return;

        const wrapper = e.target.closest('[data-industry-select]');
        const subSelect = wrapper.querySelector('[data-industry-sub]');
        const cluster = e.target.value;

        subSelect.disabled = ! cluster;
        subSelect.querySelectorAll('option').forEach(option => {
            if (! option.value) {
                option.style.display = '';
                return;
            }

            const show = option.dataset.cluster === cluster;
            option.style.display = show ? '' : 'none';
            if (! show) option.selected = false;
        });

        if (! cluster && subSelect.value) {
            subSelect.value = '';
        }
    });
</script>
