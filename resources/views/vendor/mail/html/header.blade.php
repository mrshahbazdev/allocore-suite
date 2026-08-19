@props(['url'])
@php
    $brand = config('app.team_branding') ?? [];
    $siteName = $brand['name'] ?? config('app.name', 'Allocore');
    $logo = $brand['logo'] ?? null;
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if ($logo)
<img src="{{ $logo }}" class="logo" alt="{{ $siteName }}">
@endif
<div style="color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.025em;">{{ $siteName }}</div>
</a>
</td>
</tr>
