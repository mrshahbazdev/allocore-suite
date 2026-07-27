@php
$sub = $industrySub ?? '';
$cluster = $industry ?? '';
@endphp
@if ($sub && $cluster)
    {{ $sub }} ({{ $cluster }})
@elseif ($cluster)
    {{ $cluster }}
@else
    —
@endif
