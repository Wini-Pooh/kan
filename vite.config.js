import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',  'resources/js/task-show.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: {
            origin: ['https://kanban', 'https://localhost', 'https://127.0.0.1'],
            credentials: true,
        },
        hmr: {
            host: 'localhost',
        },
    },
});
