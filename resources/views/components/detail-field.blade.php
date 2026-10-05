@props(['label', 'span' => 1])

<div @class(['detail-span-2' => (int) $span === 2])>
    <p class="detail-label">{{ $label }}</p>
    <div class="detail-value">{{ $slot }}</div>
</div>
