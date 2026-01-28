import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            // Ignora node_modules e vendor para reduzir overhead de file watching
            ignored: ['**/node_modules/**', '**/vendor/**', '**/.git/**'],
            // Usa polling para melhor compatibilidade com Docker/WSL/VM
            usePolling: true,
            interval: 1000,
        },
        warmup: {
            // Pré-aquece os arquivos principais
            clientFiles: ['./resources/js/app.js', './resources/css/app.css'],
        },
    },
    // Otimização de dependências - pré-bundling
    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/vue3',
            'axios',
        ],
        exclude: [],
    },
    // Configurações de CSS para melhor performance
    css: {
        devSourcemap: false, // Desativa sourcemaps em dev para mais velocidade
    },
    // Cache explícito para acelerar rebuilds
    cacheDir: 'node_modules/.vite',
    // Configurações de build mais eficientes
    build: {
        target: 'esnext',
        minify: 'esbuild',
        sourcemap: false,
    },
    // Evita processamento desnecessário
    esbuild: {
        target: 'esnext',
    },
});
