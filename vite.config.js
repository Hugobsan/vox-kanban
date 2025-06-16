import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { removeCSPMiddleware } from './vite-csp-middleware.js';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'],
            refresh: true,
        }),
        removeCSPMiddleware(), // Plugin customizado para remover CSP
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
    },
    // Configurações específicas para desenvolvimento
    define: {
        global: 'globalThis',
    },
});
