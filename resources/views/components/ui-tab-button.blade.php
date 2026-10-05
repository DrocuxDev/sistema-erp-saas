@props(['tab', 'label', 'active' => false])

<button type="button" data-tab="{{ $tab }}"
        @class(['tab-button', 'ui-tab', 'ui-tab-active' => $active])>
    {{ $label }}
</button>
