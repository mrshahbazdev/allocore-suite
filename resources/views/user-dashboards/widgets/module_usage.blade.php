@php($chartId = 'chart_' . uniqid())
<div class="h-48">
    <canvas id="{{ $chartId }}"></canvas>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('{{ $chartId }}');
        if (!ctx || typeof window.Chart === 'undefined') return;
        new window.Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartModules->pluck('name')) !!},
                datasets: [{
                    label: "{{ __('Records') }}",
                    data: {!! json_encode($chartModules->pluck('count')) !!},
                    backgroundColor: '#4f46e5',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { display: false } }
                }
            }
        });
    });
</script>
