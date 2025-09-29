import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/custom.css',
                'resources/js/app.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: '127.0.0.1',  // IPv4を明示的に指定
        port: 5173,
        hmr: {
            host: '127.0.0.1'  // Hot Module Reloadも IPv4 を使用
        }
    },
    resolve: {
        alias: {
            '~bootstrap': 'bootstrap',
        }
    },
});
