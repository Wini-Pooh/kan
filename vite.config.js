import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/task-editor.css',
                'resources/css/mobile-responsive.css',
                'resources/css/custom-dropdown.css',
                'resources/css/kanban-mobile.css',
                'resources/js/app.js',
                'resources/js/task-show.js',
                'resources/js/mobile-adaptive.js',
                'resources/js/kanban-mobile.js',
                'resources/js/column-mobile-optimizer.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: {
            origin: [
                'http://kanban', 
                'https://kanban', 
                'http://localhost', 
                'https://localhost', 
                'http://127.0.0.1', 
                'https://127.0.0.1',
                'http://localhost:8000',
                'http://127.0.0.1:8000'
            ],
            credentials: true,
        },
        hmr: {
            host: 'localhost',
        },
    },
});
