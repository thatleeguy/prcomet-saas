import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Geist', 'ui-sans-serif', 'system-ui', ...defaultTheme.fontFamily.sans],
                mono: ['"Geist Mono"', ...defaultTheme.fontFamily.mono],
            },
            // Brand colour — confident corporate indigo that pairs cleanly with
            // slate neutrals. Used sparingly so it stays meaningful.
            colors: {
                brand: {
                    50:  '#EEF0FF',
                    100: '#E0E3FF',
                    200: '#C3C8FE',
                    300: '#9CA3FB',
                    400: '#7178F5',
                    500: '#5152EE',
                    600: '#4339DC',
                    700: '#382EBB',
                    800: '#2D2697',
                    900: '#252177',
                },
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(15 23 42 / 0.04), 0 0 0 1px rgb(15 23 42 / 0.04)',
                'card-hover': '0 4px 12px -2px rgb(15 23 42 / 0.08), 0 0 0 1px rgb(15 23 42 / 0.06)',
            },
            keyframes: {
                'fade-in': {
                    '0%':   { opacity: '0', transform: 'translateY(4px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'draw': {
                    '0%':   { strokeDashoffset: '1000' },
                    '100%': { strokeDashoffset: '0' },
                },
            },
            animation: {
                'fade-in': 'fade-in 400ms cubic-bezier(0.16, 1, 0.3, 1) both',
                'draw':    'draw 1.2s cubic-bezier(0.5, 0, 0, 1) forwards',
            },
        },
    },

    plugins: [forms, typography],
};
