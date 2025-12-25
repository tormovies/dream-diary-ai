import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Минификация
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Удаляем console.log в продакшене
                drop_debugger: true,
            },
        },
        // Code splitting отключен - Alpine.js нужен сразу для работы меню
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
        // Оптимизация размера чанков
        chunkSizeWarningLimit: 1000,
        // Генерация sourcemaps только для dev
        sourcemap: false,
    },
    // Оптимизация для продакшена
    esbuild: {
        drop: ['console', 'debugger'],
    },
});
