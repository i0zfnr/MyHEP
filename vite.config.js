import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    const sourceName = assetInfo.originalFileNames?.[0]
                        ?? assetInfo.names?.[0]
                        ?? assetInfo.name
                        ?? '';

                    if (sourceName.endsWith('.mjs')) {
                        return 'assets/[name]-[hash].js';
                    }

                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/design-system.css', 'resources/js/app.js', 'resources/js/certificate-template-editor.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
