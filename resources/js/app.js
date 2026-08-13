// Register the PWA service worker automatically via Vite plugin injection
import { registerSW } from 'virtual:pwa-register';

if ('serviceWorker' in navigator) {
    registerSW({
        onNeedRefresh() {
            console.log('Novas atualizações disponíveis no Combinadinhos! Recarregando...');
        },
        onOfflineReady() {
            console.log('Combinadinhos pronto para uso offline! 🤝');
        },
    });
}
