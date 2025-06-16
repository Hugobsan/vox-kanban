import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Debug: verificar variáveis de ambiente
console.log('Reverb Config:', {
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: import.meta.env.VITE_REVERB_HOST,
    port: import.meta.env.VITE_REVERB_PORT,
    scheme: import.meta.env.VITE_REVERB_SCHEME
});

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    // Configuração de autenticação para Laravel com sessões
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json',
        },
    },
});

// Debug: adicionar logs dos eventos do Echo
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Echo connected to Reverb');
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('❌ Echo disconnected from Reverb');
});

window.Echo.connector.pusher.connection.bind('error', (error) => {
    console.error('❌ Echo connection error:', error);
});

// Debug: adicionar event listeners para conexão
window.Echo.connector.pusher.connection.bind('connected', function() {
    console.log('✅ Echo connected to Reverb successfully!');
});

window.Echo.connector.pusher.connection.bind('disconnected', function() {
    console.log('❌ Echo disconnected from Reverb');
});

window.Echo.connector.pusher.connection.bind('error', function(error) {
    console.log('🚨 Echo connection error:', error);
});

window.Echo.connector.pusher.connection.bind('state_change', function(states) {
    console.log('🔄 Echo state change:', states);
});

console.log('Echo initialized:', window.Echo);
