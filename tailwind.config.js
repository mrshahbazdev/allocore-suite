import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './Modules/**/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand identity: orange (#ff9200) replaces indigo, blue (#0094af) replaces blue
                indigo: {
                    50: '#ffe6cc',
                    100: '#ffd199',
                    200: '#ffbc66',
                    300: '#ffa733',
                    400: '#ff9a11',
                    500: '#ff9200',
                    600: '#e68300',
                    700: '#bf6d00',
                    800: '#995700',
                    900: '#724100',
                    950: '#472800',
                },
                blue: {
                    50: '#e6f6fa',
                    100: '#bfe8f1',
                    200: '#99dae8',
                    300: '#66c7dc',
                    400: '#33b4d0',
                    500: '#00a1c4',
                    600: '#0094af',
                    700: '#007d93',
                    800: '#006677',
                    900: '#004e5c',
                    950: '#003740',
                },
                brand: {
                    primary: '#ff9200',
                    secondary: '#0094af',
                    dark: '#0f172a',
                },
                page: 'var(--color-page-bg, #ffffff)',
                card: 'var(--color-card-bg, #f5f7fa)',
                txmain: 'var(--color-text-main, #4a4a4a)',
                heading: '#1f2937',
                darkbox: '#ff8c00',
                unpaid: {
                    text: '#ff8c00',
                    bg: '#ff8c001a',
                },
            },
        },
    },

    plugins: [forms],
};
