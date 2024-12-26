import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
// import react from '@vitejs/plugin-react';
import reactPlugin from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            buildDirectory: 'build',
        }),
        reactPlugin({
            fastRefresh: true
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost'
        },
        watch: {
            usePolling: true,
        },
    },
    build: {
        outDir: 'public/build',
        manifest: 'manifest.json',
        cssCodeSplit: true,
        rollupOptions: {
            input: path.resolve(__dirname, 'resources/js/app.tsx'),
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.css')) {
                        return 'assets/[name][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                }
            }
        },
        sourcemap: true
    },
    optimizeDeps: {
        include: ['@radix-ui/react-slot', 'class-variance-authority', 'clsx', 'tailwind-merge']
    },
});
