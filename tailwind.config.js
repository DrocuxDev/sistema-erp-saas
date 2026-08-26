import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    dark: '#0B1329',
                    teal: '#0891B2',
                    cyan: '#06B6D4',
                    emerald: '#10B981',
                    muted: '#64748B',
                },
                // Paleta Oficial ERP: Deep Slate + Neon Cyan + Neon Emerald
                'dark-bg': '#0B1329',
                'darker-bg': '#060B18',
                'sidebar-bg': '#0D1527',
                'card-bg': '#111C35',
                'card-border': '#1E293B',
                'cyan-primary': '#06B6D4',
                'cyan-accent': '#22D3EE',
                'emerald-accent': '#10B981',
                'purple-primary': '#06B6D4', // Mapeo de compatibilidad hacia Cyan Neón
                'purple-light': '#38BDF8',
                'pink-accent': '#06B6D4',
                'blue-accent': '#0284C7',
            },
        },
    },

    plugins: [forms],
};
