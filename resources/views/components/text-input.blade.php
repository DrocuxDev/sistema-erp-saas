@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white dark:bg-darker-bg border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:border-cyan-500 focus:ring-cyan-500 rounded-lg text-sm shadow-2xs placeholder-gray-400 dark:placeholder-gray-500']) }}>
