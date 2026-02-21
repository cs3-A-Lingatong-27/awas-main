import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // Add the JS file back into the input array here:
            input: [
                'resources/css/app.css', 
                'resources/js/build-placeholder.js' 
            ],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1600,
    }
});