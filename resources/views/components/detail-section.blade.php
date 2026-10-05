@props(['title'])

<section class="detail-section">
    <h3 class="detail-section-title">{{ $title }}</h3>
    <div class="detail-grid">
        {{ $slot }}
    </div>
</section>
