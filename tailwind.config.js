/** @type {import('tailwindcss').Config} */
import preset from './vendor/filament/support/tailwind.config.preset';

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    dark: 'class',
    theme: {
        extend: {},
    },
    plugins: [
        //require('@tailwindcss/forms'),
    ],
}


