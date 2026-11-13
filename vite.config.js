import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

// https://vitejs.dev/config/
export default defineConfig(() => {
    const primaryUrl = process.env.DDEV_PRIMARY_URL || 'http://localhost';
    const origin = primaryUrl.replace(/:\d+$/, '') + ':5173';

    return {
        plugins: [
            vue({
                // Absolute Pfade (/_resources/...) werden von Silverstripe ausgeliefert und nicht gebündelt
                template: { transformAssetUrls: { includeAbsolute: false } },
            }),
        ],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./app/client/src/js', import.meta.url)),
            },
        },
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            origin,
            cors: {
                origin: /https?:\/\/([A-Za-z0-9\-.]+)?(\.ddev\.site)(?::\d+)?$/,
            },
            watch: {
                usePolling: true,
            },
        },
        base: './',
        publicDir: 'app/client/public',
        build: {
            outDir: './app/client/dist',
            emptyOutDir: true,
            manifest: true,
            sourcemap: true,
            rollupOptions: {
                input: {
                    'main.js': './app/client/src/js/main.js',
                    'main.scss': './app/client/src/scss/main.scss',
                    'editor.scss': './app/client/src/scss/editor.scss',
                },
            },
        },
        css: {
            devSourcemap: true,
        },
    };
});
