import { defineConfig } from 'vite';
import { silverstripe } from '@silverstripe/vite-plugin';

export default defineConfig({
  plugins: [
    silverstripe({
      // SilverStripe Vite Plugin configuration
      input: {
        // Main styles
        'main': 'app/client/scss/main.scss',
        
        // Wishlist specific assets
        'wishlist': 'app/client/js/wishlist.js',
        'wishlist-guest': 'app/client/js/wishlist-guest.js',
        'wishlist-styles': 'app/client/scss/wishlist.scss'
      },
      
      // Asset output directory
      outDir: 'public/resources',
      
      // Enable hot module replacement in development
      hmr: true
    })
  ],
  
  // Development server configuration
  server: {
    port: 5173,
    host: true,
    cors: true,
    hmr: {
      port: 5173
    }
  },
  
  // Build configuration
  build: {
    sourcemap: true,
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          const info = assetInfo.name.split('.');
          const extType = info[info.length - 1];
          if (/\.(css)$/.test(assetInfo.name)) {
            return `css/[name]-[hash][extname]`;
          }
          if (/\.(png|jpe?g|svg|gif|tiff|bmp|ico)$/i.test(assetInfo.name)) {
            return `images/[name]-[hash][extname]`;
          }
          return `assets/[name]-[hash][extname]`;
        },
        chunkFileNames: 'js/[name]-[hash].js',
        entryFileNames: 'js/[name]-[hash].js'
      }
    }
  },
  
  // CSS preprocessing
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@import "app/client/scss/variables.scss";`
      }
    }
  }
});