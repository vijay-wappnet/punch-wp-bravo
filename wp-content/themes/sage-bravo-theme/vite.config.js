import { defineConfig, loadEnv } from 'vite'
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';

// Vite doesn't load .env into process.env on its own, so read it here
const fileEnv = loadEnv(process.env.NODE_ENV ?? 'development', process.cwd(), ['APP_URL', 'DEV_SERVER_HOST']);

// Set APP_URL if it doesn't exist for Laravel Vite plugin
process.env.APP_URL ||= fileEnv.APP_URL || 'http://localhost/punch-wp-bravo';

// `npm run dev` on the office network: DEV_SERVER_HOST (in .env) is this PC's
// LAN IP. The dev server listens on all interfaces and WordPress loads the dev
// assets from http://DEV_SERVER_HOST:5173, so they work on this PC and on other
// office PCs alike. Without it, the dev server stays local-only.
const devHost = fileEnv.DEV_SERVER_HOST || '';
const escapeRegex = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

export default defineConfig({
  //base: '/app/themes/sage/public/build/',
  base: '/wp-content/themes/sage-bravo-theme/public/build/',
  plugins: [
    laravel({
      input: [
        'resources/css/app.scss',
        'resources/js/app.js',
        'resources/css/editor.scss',
        'resources/js/editor.js',
      ],
      refresh: true,
      assets: ['resources/images/**', 'resources/fonts/**'],
    }),

    wordpressPlugin(),

    // Generate the theme.json file in the public/build/assets directory
    // based on the Tailwind config and the theme.json file from base theme folder
    wordpressThemeJson({
      disableTailwindColors: true,
      disableTailwindFonts: true,
      disableTailwindFontSizes: true,
      disableTailwindBorderRadius: true,
    }),
  ],
  server: devHost ? {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    // Written to public/hot, so WordPress points asset URLs here
    origin: `http://${devHost}:5173`,
    // Pages may be opened on localhost or on the LAN IP
    cors: {
      origin: [
        /^https?:\/\/(?:(?:[^:]+\.)?localhost|127\.0\.0\.1|\[::1\])(?::\d+)?$/,
        new RegExp(`^https?://${escapeRegex(devHost)}(?::\\d+)?$`),
      ],
    },
  } : undefined,
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        // Bootstrap's own SCSS still uses @import/global color functions,
        // which spam deprecation warnings under newer Dart Sass.
        // This silences warnings coming from node_modules only, not our own scss.
        quietDeps: true,
      },
    },
  },
})
