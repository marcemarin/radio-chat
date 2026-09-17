import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export function createEcho() {
    const r = window.RADIO.reverb;
    return new Echo({
        broadcaster: 'reverb',
        key: r.key,
        wsHost: r.host,
        wsPort: r.port,
        wssPort: r.port,
        forceTLS: r.scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
