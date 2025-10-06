import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            // Ajusta la ruta de entrada para el CSS si es necesario
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        extensions: ['.js', '.jsx', '.ts', '.tsx'],
        alias: {
            // El alias '@' apuntará a la carpeta 'resources/js'
            '@': '/resources/js',
            // El alias '@res' apuntará a la carpeta raíz 'resources'
            '@res': '/resources',
            '@css': '/resources/css',
        },
    },
    esbuild: {
        jsx: 'automatic',
    },
});
