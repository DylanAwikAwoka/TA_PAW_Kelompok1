import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [react()],
    server: {
        port: 5173,
        // Supaya fetch('/api/...') diteruskan ke Laravel saat pengembangan (tanpa masalah CORS).
        proxy: {
            '/api': 'http://127.0.0.1:8000',
        },
    },
});
