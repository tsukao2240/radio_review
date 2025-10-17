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
    build: {
        // 本番環境向け最適化
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // console.logを削除
                drop_debugger: true, // debuggerを削除
            },
        },
        rollupOptions: {
            output: {
                // チャンク分割によるバンドルサイズ最適化
                manualChunks: {
                    'react-vendor': ['react', 'react-dom'],
                    'bootstrap': ['bootstrap'],
                },
            },
        },
        // ソースマップは開発環境のみ
        sourcemap: false,
        // チャンクサイズ警告の閾値
        chunkSizeWarningLimit: 1000,
    },
});
