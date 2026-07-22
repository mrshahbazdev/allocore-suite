@extends('layouts.shell', ['title' => __('Org Chart')])

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $organization->name }} — {{ __('Org Chart') }}</h1>
        <a href="{{ route('orgmatrix.organizations.show', $organization) }}" class="text-indigo-600 hover:underline">{{ __('Back') }}</a>
    </div>

    <div id="chart" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm overflow-auto min-h-[400px]"></div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
const data = @json($tree);

function renderTree(rootData) {
    const container = document.getElementById('chart');
    container.innerHTML = '';

    const margin = {top: 20, right: 90, bottom: 30, left: 90};
    const width = Math.max(container.clientWidth, 800) - margin.left - margin.right;
    const height = 500 - margin.top - margin.bottom;

    const svg = d3.select('#chart').append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);

    const root = d3.hierarchy({name: '{{ $organization->name }}', children: rootData});
    const treeLayout = d3.tree().size([height, width]);
    treeLayout(root);

    const link = svg.selectAll('.link')
        .data(root.links())
        .enter().append('path')
        .attr('class', 'link')
        .attr('fill', 'none')
        .attr('stroke', '#cbd5e1')
        .attr('stroke-width', 1.5)
        .attr('d', d3.linkHorizontal().x(d => d.y).y(d => d.x));

    const node = svg.selectAll('.node')
        .data(root.descendants())
        .enter().append('g')
        .attr('class', 'node')
        .attr('transform', d => `translate(${d.y},${d.x})`);

    node.append('circle')
        .attr('r', 6)
        .attr('fill', d => d.data.criticality === 'critical' ? '#f43f5e' : d.data.criticality === 'high' ? '#f59e0b' : '#3b82f6');

    node.append('text')
        .attr('dy', '.35em')
        .attr('x', d => d.children ? -13 : 13)
        .attr('text-anchor', d => d.children ? 'end' : 'start')
        .text(d => d.data.name + (d.data.person ? ' — ' + d.data.person.name : ''))
        .style('font-size', '12px')
        .style('fill', '#1e293b');
}

if (data.length === 0) {
    document.getElementById('chart').innerHTML = '<p class="p-4 text-slate-500">{{ __('No roles to display.') }}</p>';
} else {
    renderTree(data);
}
</script>
@endsection
