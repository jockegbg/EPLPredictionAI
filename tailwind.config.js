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
                'pl-purple': '#37003c',
                'pl-green': '#00ff85',
                'pl-pink': '#ff2882',
                'pl-blue': '#04f5ff',
                'pl-dark': '#1c0c24',
            },
            backgroundImage: {
                'pl-gradient': 'linear-gradient(135deg, #37003c 0%, #2f0034 100%)',
            }
        },
    },

    plugins: [forms],
};
