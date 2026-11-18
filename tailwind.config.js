import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Noto Color Emoji'],
            },
            colors: {
                bg: 'rgb(var(--c-bg) / <alpha-value>)',
                surface: 'rgb(var(--c-surface) / <alpha-value>)',
                surface2: 'rgb(var(--c-surface2) / <alpha-value>)',
                line: 'rgb(var(--c-line) / <alpha-value>)',
                ink: 'rgb(var(--c-ink) / <alpha-value>)',
                muted: 'rgb(var(--c-muted) / <alpha-value>)',
                primary: 'rgb(var(--c-primary) / <alpha-value>)',
                primarydark: 'rgb(var(--c-primarydark) / <alpha-value>)',
                accent: 'rgb(var(--c-accent) / <alpha-value>)',
                danger: 'rgb(var(--c-danger) / <alpha-value>)',
                info: 'rgb(var(--c-info) / <alpha-value>)',
            },
            borderRadius: {
                card: '12px',
                btn: '10px',
                input: '10px',
                chip: '999px',
            },
            maxWidth: {
                content: '1120px',
            },
        },
    },

    plugins: [forms],
};
