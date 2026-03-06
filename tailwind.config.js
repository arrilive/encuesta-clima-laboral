import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'from-blue-600',    'to-blue-500',
        'from-emerald-600', 'to-emerald-500',
        'from-amber-500',   'to-amber-400',
        'from-rose-600',    'to-rose-500',
        'from-violet-600',  'to-violet-500',
        'from-cyan-600',    'to-cyan-500',
        'bg-gradient-to-r',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms],
};