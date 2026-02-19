import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    base: './', // Rutas relativas - funciona en cualquier subdirectorio
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/css/admin.css',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                // Asegurar rutas relativas en assets
                assetFileNames: 'assets/[name]-[hash][extname]'
            }
        }
    },
    assetsInclude: ['**/*.woff2', '**/*.woff', '**/*.ttf', '**/*.eot'],
});