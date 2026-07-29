@php($height = in_array($block['height'] ?? '', ['small', 'medium', 'large']) ? $block['height'] : 'medium')
@php($heightClass = match($height) { 'small' => 'py-8 lg:py-12', 'large' => 'py-24 lg:py-32', default => 'py-16 lg:py-24' })
<section class="{{ $heightClass }}"></section>
