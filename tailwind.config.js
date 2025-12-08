import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Urbanist', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                primary: '#F2676A',
                bluemain: '#172139',
                slate: '#263859',
                muted: '#6b778d',
                silver: '#929291',
            },
        },
    },

    plugins: [forms],
};
