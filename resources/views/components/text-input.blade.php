@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-darker-bg border-gray-600 text-white focus:border-purple-primary focus:ring-purple-primary rounded-md shadow-sm placeholder-gray-400']) }}>
