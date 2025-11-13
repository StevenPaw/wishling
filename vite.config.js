import {defineConfig} from 'vite'

// https://vitejs.dev/config/
export default defineConfig(({ command }) => {
    const primary_url = process.env.DDEV_PRIMARY_URL || 'http://localhost';
    const origin = primary_url.replace(/:\d+$/, "") + `:5173`;
    return {
        server: {
            host: "0.0.0.0",
            port: 5173,
            strictPort: true,
            origin: origin,
            cors: {
                origin: /https?:\/\/([A-Za-z0-9\-\.]+)?(\.ddev\.site)(?::\d+)?$/,
            },
            watch: {
                usePolling: true, // Enable polling for file changes
            }
        },
        alias: {
            alias: [{find: '@', replacement: './app/client/src'}],
        },
        publicDir: 'app/client/public',
        build: {
            outDir: './app/client/dist',
            manifest: true,
            sourcemap: true,
            rollupOptions: {
                input: {
                    'main.js': './app/client/src/js/main.js',
                    'main.scss': './app/client/src/scss/main.scss',
                    'wishlist.js': './app/client/src/js/wishlist.js',
                    'wishlist-guest.js': './app/client/src/js/wishlist-guest.js',
                    'editor.scss': './app/client/src/scss/editor.scss'
                },
            },
        },
        css: {
            devSourcemap: true,
            preprocessorOptions: {
                scss: {
                    // Using @use instead of @import, no auto-injection needed
                    api: 'modern-compiler' // Use modern Sass API
                }
            }
        },
        plugins: []
    }
})
