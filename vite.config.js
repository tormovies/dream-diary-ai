import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/articles.css', 'resources/js/app.js', 'resources/js/tinymce-init.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            // Убеждаемся, что TinyMCE правильно резолвится
        }
    },
    build: {
        // Минификация
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Удаляем console.log в продакшене
                drop_debugger: true,
            },
        },
        // CSS code splitting для оптимизации
        cssCodeSplit: true,
        // Code splitting для оптимизации загрузки
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Разделить vendor на отдельные чанки для лучшего кеширования
                    if (id.includes('node_modules')) {
                        if (id.includes('alpinejs')) {
                            return 'alpine';
                        }
                        if (id.includes('fontawesome')) {
                            return 'fontawesome';
                        }
                        if (id.includes('axios')) {
                            return 'axios';
                        }
                        return 'vendor';
                    }
                },
            },
        },
        // Оптимизация размера чанков
        chunkSizeWarningLimit: 500,
        // Генерация sourcemaps только для dev
        sourcemap: false,
    },
    // Оптимизация для продакшена
    esbuild: {
        drop: ['console', 'debugger'],
    },
    // Настройка dev сервера
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },
});
