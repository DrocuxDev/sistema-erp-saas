@props(['name', 'value' => '', 'placeholder' => '', 'required' => false, 'maxlength' => null, 'autofocus' => false])

<input
    type="text"
    name="{{ $name }}"
    value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}"
    @if($required) required @endif
    @if($autofocus) autofocus @endif
    @if($maxlength) maxlength="{{ $maxlength }}" @endif
    class="ui-title"
/>
<x-input-error :messages="$errors->get($name)" class="mt-2" />
