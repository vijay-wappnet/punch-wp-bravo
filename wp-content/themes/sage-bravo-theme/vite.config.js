import { defineConfig, loadEnv } from 'vite'
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';

// Vite doesn't load .env into process.env on its own, so read it here
const fileEnv = loadEnv(process.env.NODE_ENV ?? 'development', process.cwd(), 'APP_URL');

// Set APP_URL if it doesn't exist for Laravel Vite plugin
process.env.APP_URL ||= fileEnv.APP_URL || 'http://localhost/punch-wp-bravo';

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
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
})
