import { defineConfig } from 'vite';
import tailwindcss from 'tailwindcss';

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
    server: {
        // ...other server options

        host: 'localhost', // Host for HMR in development mode
        port: 4000, // Port for HMR in development mode

    },
})