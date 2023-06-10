import { defineConfig } from 'vite';

/** @type {import('vite').UserConfig} */
export default defineConfig({
    build: {
        copyPublicDir: false,
        outDir: './public/build',
        manifest: true,
        rollupOptions: {
            input: [
                '/assets/css/app.css',
                '/assets/js/app.js',
            ],
        },
    },
})