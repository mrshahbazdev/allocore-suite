@php
    $height = in_array($block['height'] ?? '', ['small', 'medium', 'large']) ? $block['height'] : 'medium';
    $heightClass = match($height) { 'small' => 'py-8 lg:py-12', 'large' => 'py-24 lg:py-32', default => 'py-16 lg:py-24' };
@endphp
<section class="{{ $heightClass }}"></section>
