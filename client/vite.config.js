import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue2'
import devManifest from 'vite-plugin-dev-manifest';

export default defineConfig({
  esbuild: {
    minify: true,
    minifySyntax: true
  },
  plugins: [
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    devManifest(),
  ],
  base: '/wp-content/plugins/hello2-forms/assets/client/',
  build: {
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      // specify your input files here, as stated in Vite config https://vitejs.dev/config/#build-rollupoptions
      input: './src/app.js',
    },
    outDir: '../hello2-forms/assets/client',
  },
  optimizeDeps: {
    entries: [],
    exclude: [
      'vt-notifications', 'vue-tailwind', 'vue-tailwind/dist/vue-tailwind.css'
    ]
  },
  resolve: {
    alias: {
      '~': '/src',
      '@': '/src'
    }
  }
})
