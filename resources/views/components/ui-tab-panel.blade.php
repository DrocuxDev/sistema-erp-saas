@props(['tab', 'active' => false])

<div id="tab-{{ $tab }}" @class(['tab-content', 'hidden' => !$active])>
    {{ $slot }}
</div>
