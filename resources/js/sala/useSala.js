import { useEffect, useMemo, useState } from 'react';
import { api } from './api.js';
import { createEcho } from './echo.js';

export const INTENT = {
    reclamo: 'Reclamo', pedido_musical: 'Pedido musical', saludo: 'Saludo', opinion: 'Opinión',
    concurso: 'Concurso', consulta: 'Consulta', spam: 'Spam', otro: 'Otro',
};
export const FILTERS = [['all', 'Todo'], ['audio', 'Audios'], ['reclamo', 'Reclamos'], ['pedido_musical', 'Pedidos'], ['concurso', 'Concurso'], ['consulta', 'Consultas'], ['saludo', 'Saludos'], ['opinion', 'Opiniones']];
export const STATUS_TEXT = { received: 'recibido', media: 'descargando el audio', transcribing: 'transcribiendo', classifying: 'leyendo' };

export const ago = (iso) => {
    const s = Math.max(0, (Date.now() - new Date(iso)) / 1000);
    return s < 45 ? 'ahora' : s < 3600 ? `hace ${Math.floor(s / 60)} min` : `hace ${Math.floor(s / 3600)} h`;
};
export const mmss = (s) => `${Math.floor(s / 60)}:${String(Math.floor(s % 60)).padStart(2, '0')}`;
export const who = (m) => (m.location ? `${m.contact.name}, ${m.location}` : m.contact.name);
export const text = (m) => m.transcript ?? m.body;
const normTopic = (t) => (t || '').toLowerCase().replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trim();

/** Estado compartido por las dos pantallas: mensajes, cola al aire, temas, conexión. */
export function useSala() {
    const [program, setProgram] = useState(null);
    const [messages, setMessages] = useState([]);
    const [highlights, setHighlights] = useState([]);
    const [connected, setConnected] = useState(false);
    const [, tick] = useState(0);

    useEffect(() => { const t = setInterval(() => tick((n) => n + 1), 15_000); return () => clearInterval(t); }, []);

    const upsert = (m) => setMessages((prev) => {
        const i = prev.findIndex((x) => x.id === m.id);
        if (i === -1) return [m, ...prev].slice(0, 500);
        const next = prev.slice(); next[i] = m; return next;
    });
    const reloadHighlights = (pid) => api.highlights(pid).then((r) => setHighlights(r.highlights));

    useEffect(() => {
        let echo;
        (async () => {
            const p = await api.program();
            setProgram(p);
            const [{ messages }, { highlights }] = await Promise.all([api.messages(p.id, { limit: 200 }), api.highlights(p.id)]);
            setMessages(messages); setHighlights(highlights);
            echo = createEcho();
            echo.connector.pusher.connection.bind('state_change', ({ current }) => setConnected(current === 'connected'));
            echo.channel(`sala.${p.id}`)
                .listen('.message.received', ({ message }) => upsert(message))
                .listen('.message.updated', ({ message }) => { upsert(message); reloadHighlights(p.id); });
        })();
        return () => echo?.disconnect();
    }, []);

    const counts = useMemo(() => {
        const c = { all: 0, audio: 0 };
        for (const m of messages) {
            if (m.moderation?.includes('spam')) continue;
            c.all++; if (m.type === 'audio') c.audio++;
            if (m.intent) c[m.intent] = (c[m.intent] || 0) + 1;
        }
        return c;
    }, [messages]);

    const topics = useMemo(() => {
        const cutoff = Date.now() - 3600_000, map = new Map();
        for (const m of messages) {
            if (!m.topic_label || new Date(m.sent_at) < cutoff) continue;
            const k = normTopic(m.topic_label);
            const e = map.get(k) || { label: m.topic_label, n: 0 };
            e.n++; map.set(k, e);
        }
        return [...map.values()].filter((t) => t.n >= 2).sort((a, b) => b.n - a.n).slice(0, 5);
    }, [messages]);

    const onAir = highlights.find((h) => h.status === 'on_air') ?? null;
    const queue = highlights.filter((h) => h.status === 'pending');

    const actions = {
        highlight: async (m) => { await api.highlight(m.id); reloadHighlights(program.id); },
        setStatus: async (h, status) => { await api.updateHighlight(h.id, { status }); reloadHighlights(program.id); },
        next: async () => { const r = await api.next(program.id); setHighlights(r.highlights); },
        badTranscript: (m) => api.feedback(m.id, true),
    };

    return { program, messages, highlights, onAir, queue, counts, topics, connected, actions };
}

export function filterMessages(messages, filter) {
    return messages.filter((m) => {
        if (m.moderation?.includes('spam')) return filter === 'spam';
        if (filter === 'all') return true;
        if (filter === 'audio') return m.type === 'audio';
        if (filter === 'best') return (m.on_air_score ?? 0) >= 60 && !m.highlight;
        return m.intent === filter;
    });
}
