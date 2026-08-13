<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-purple-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-light focus:bg-purple-light active:bg-purple-primary focus:outline-none focus:ring-2 focus:ring-purple-primary focus:ring-offset-2 focus:ring-offset-dark-bg transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
