@extends('layouts.shell', ['title' => __('Org Chart')])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ $organization->name }}</h1>
            <p class="text-sm text-slate-500">{{ __('Interactive organization hierarchy') }}</p>
        </div>
        <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            {{ __('Back to Organization') }}
        </a>
    </div>

    <div id="chart" class="min-h-[500px] overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"></div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
const data = @json($tree);

function renderTree(rootData) {
    const container = document.getElementById('chart');
    container.innerHTML = '';

    const margin = {top: 40, right: 120, bottom: 40, left: 120};
    const width = Math.max(container.clientWidth, 900) - margin.left - margin.right;
    const height = 500 - margin.top - margin.bottom;

    const svg = d3.select('#chart').append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);

    const root = d3.hierarchy({name: '{{ $organization->name }}', children: rootData});
    const treeLayout = d3.tree().size([height, width]);
    treeLayout(root);

    svg.selectAll('.link')
        .data(root.links())
        .enter().append('path')
        .attr('class', 'link')
        .attr('fill', 'none')
        .attr('stroke', '#cbd5e1')
        .attr('stroke-width', 2)
        .attr('d', d3.linkHorizontal().x(d => d.y).y(d => d.x));

    const node = svg.selectAll('.node')
        .data(root.descendants())
        .enter().append('g')
        .attr('class', 'node')
        .attr('transform', d => `translate(${d.y},${d.x})`);

    node.append('circle')
        .attr('r', 8)
        .attr('fill', d => d.data.criticality === 'critical' ? '#f43f5e' : d.data.criticality === 'high' ? '#f59e0b' : '#4f46e5')
        .attr('stroke', '#fff')
        .attr('stroke-width', 2);

    node.append('text')
        .attr('dy', '.35em')
        .attr('x', d => d.children ? -16 : 16)
        .attr('text-anchor', d => d.children ? 'end' : 'start')
        .text(d => d.data.name + (d.data.person ? ' — ' + d.data.person.name : ''))
        .style('font-size', '13px')
        .style('font-weight', '600')
        .style('fill', '#1e293b');
}

if (data.length === 0) {
    document.getElementById('chart').innerHTML = `
        <div class="flex h-full flex-col items-center justify-center p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('No roles to display') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('Add roles with parent relationships to see the hierarchy.') }}</p>
        </div>
    `;
} else {
    renderTree(data);
}
</script>
@endsection
