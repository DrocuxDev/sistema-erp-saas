<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-cyan-600 dark:bg-cyan-500 hover:bg-cyan-700 dark:hover:bg-cyan-600 text-white rounded-lg font-medium text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-cyan-500/30 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
