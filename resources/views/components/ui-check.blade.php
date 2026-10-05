@props(['name', 'label', 'checked' => false, 'value' => '1'])

<label class="flex items-center cursor-pointer">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ old($name, $checked) ? 'checked' : '' }}
        class="ui-check"
    />
    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
</label>
