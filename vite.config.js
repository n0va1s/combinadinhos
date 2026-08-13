import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'inline',
            manifest: {
                name: 'Combinadinhos',
                short_name: 'Combinadinhos',
                description: 'Crie hábitos saudáveis e combinados em família',
                theme_color: '#0f172a',
                background_color: '#0f172a',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/',
                icons: [
                    {
                        src: 'https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=192&auto=format&fit=crop',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: 'https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=512&auto=format&fit=crop',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            }
        })
    ],
});
