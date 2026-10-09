// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',           // Vite menerima koneksi dari luar
        port: 5173,
        strictPort: true,
        hmr: {
            host: '192.168.34.128',  // ← ganti dengan IP laptop kamu (ipconfig)
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
