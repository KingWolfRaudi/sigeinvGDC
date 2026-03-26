import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss', // <-- Cambiamos css por sass y .css por .scss
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});