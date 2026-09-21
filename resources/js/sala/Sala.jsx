import React, { useEffect, useMemo, useRef, useState } from 'react';
import { api } from './api.js';
import { createEcho } from './echo.js';

const INTENT = {
    reclamo: 'Reclamo', pedido_musical: 'Pedido musical', saludo: 'Saludo', opinion: 'Opinión',
    concurso: 'Concurso', consulta: 'Consulta', spam: 'Spam', otro: 'Otro',
};
const FILTERS = [['all', 'Todo'], ['audio', 'Audios'], ['reclamo', 'Reclamos'], ['pedido_musical', 'Pedidos'], ['concurso', 'Concurso'], ['consulta', 'Consultas'], ['saludo', 'Saludos'], ['opinion', 'Opiniones']];
const STATUS_TEXT = { received: 'recibido', media: 'descargando el audio', transcribing: 'transcribiendo', classifying: 'leyendo' };

const ago = (iso) => {
    const s = Math.max(0, (Date.now() - new Date(iso)) / 1000);
    return s < 45 ? 'ahora' : s < 3600 ? `hace ${Math.floor(s / 60)} min` : `hace ${Math.floor(s / 3600)} h`;
};
const mmss = (s) => `${Math.floor(s / 60)}:${String(Math.floor(s % 60)).padStart(2, '0')}`;
const normTopic = (t) => (t || '').toLowerCase().replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trim();
const who = (m) => m.location ? `${m.contact.name}, ${m.location}` : m.contact.name;

export default function Sala() {
    const [program, setProgram] = useState(null);
    const [messages, setMessages] = useState([]);
    const [highlights, setHighlights] = useState([]);
    const [filter, setFilter] = useState('all');
    const [connected, setConnected] = useState(false);
    const [, tick] = useState(0);
    const echoRef = useRef(null);

    useEffect(() => { const t = setInterval(() => tick((n) => n + 1), 30_000); return () => clearInterval(t); }, []);

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
            echo = createEcho(); echoRef.current = echo;
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

    const visible = useMemo(() => messages.filter((m) => {
        if (m.moderation?.includes('spam')) return false;
        if (filter === 'all') return true;
        if (filter === 'audio') return m.type === 'audio';
        return m.intent === filter;
    }), [messages, filter]);

    const topics = useMemo(() => {
        const cutoff = Date.now() - 3600_000, map = new Map();
        for (const m of messages) {
            if (!m.topic_label || new Date(m.sent_at) < cutoff) continue;
            const k = normTopic(m.topic_label);
            const e = map.get(k) || { label: m.topic_label, n: 0, last: m.sent_at };
            e.n++; if (m.sent_at > e.last) e.last = m.sent_at; map.set(k, e);
        }
        return [...map.values()].filter((t) => t.n >= 2).sort((a, b) => b.n - a.n).slice(0, 6);
    }, [messages]);

    const onAir = highlights.find((h) => h.status === 'on_air');
    const queue = highlights.filter((h) => h.status === 'pending');
    const onHighlight = async (m) => { await api.highlight(m.id); reloadHighlights(program.id); };
    const setHl = async (h, status) => { await api.updateHighlight(h.id, { status }); reloadHighlights(program.id); };

    return (
        <div className="h-screen grid grid-cols-[200px_minmax(0,1fr)_440px]">
            <nav className="border-r border-line flex flex-col">
                <div className="px-5 pt-5 pb-4">
                    <div className="text-[22px] font-semibold leading-tight tracking-tight">{program?.name ?? '…'}</div>
                    <div className="text-mute text-sm mt-0.5">{program?.station}</div>
                </div>
                <ul className="px-2">
                    {FILTERS.map(([k, label]) => (
                        <li key={k}>
                            <button onClick={() => setFilter(k)}
                                className={`w-full flex items-baseline justify-between px-3 py-1.5 rounded-md text-[15px] ${filter === k ? 'bg-surface text-ink' : 'text-ink-2 hover:text-ink'}`}>
                                <span>{label}</span>
                                <span className="text-mute text-sm tabular-nums">{counts[k] || ''}</span>
                            </button>
                        </li>
                    ))}
                </ul>
                <div className="mt-auto px-5 py-4 text-sm text-mute flex items-center gap-2">
                    <span className={`inline-block w-2 h-2 rounded-full ${connected ? 'bg-emerald-400' : 'bg-air'}`} />
                    {connected ? 'En vivo' : 'Reconectando'}
                </div>
            </nav>

            <main className="flex flex-col min-h-0">
                {topics.length > 0 && (
                    <div className="px-6 py-3 border-b border-line flex items-baseline gap-x-5 gap-y-1 flex-wrap text-[15px]">
                        <span className="text-mute">Ahora hablan de</span>
                        {topics.map((t) => (
                            <span key={t.label} className="whitespace-nowrap"><span className="font-semibold tabular-nums">{t.n}</span> <span className="text-ink-2">{t.label}</span></span>
                        ))}
                    </div>
                )}
                <div className="flex-1 overflow-y-auto">
                    {visible.length === 0 && <p className="text-mute px-6 py-16 text-center">Todavía no hay mensajes en este filtro. Cuando lleguen, aparecen solos.</p>}
                    {visible.map((m) => <Row key={m.id} m={m} onHighlight={onHighlight} />)}
                </div>
            </main>

            <aside className="border-l border-line flex flex-col min-h-0 bg-surface">
                <div className="px-6 pt-5 pb-4 flex items-center gap-3 border-b border-line">
                    <span className={`w-3.5 h-3.5 rounded-full ${onAir ? 'bg-air lamp' : 'bg-line'}`} />
                    <span className="text-[13px] font-semibold tracking-[0.18em] text-ink-2">ON AIR</span>
                    <span className="ml-auto text-sm text-mute">{queue.length > 0 ? `${queue.length} en cola` : ''}</span>
                </div>

                <div className="flex-1 overflow-y-auto">
                    {onAir ? (
                        <section className="px-6 py-5 border-b border-line">
                            <div className="text-[15px] text-ink-2 mb-2">{who(onAir.message)}</div>
                            <p className="text-[30px] leading-[1.2] font-medium tracking-tight">{onAir.message.transcript ?? onAir.message.body}</p>
                            {onAir.message.type === 'audio' && <Player m={onAir.message} big />}
                            {onAir.message.type === 'image' && onAir.message.media_url && <img className="mt-3 max-h-64 rounded-md" src={onAir.message.media_url} alt="" />}
                            <div className="flex gap-2 mt-4">
                                <Btn onClick={() => setHl(onAir, 'done')} primary>Ya salió</Btn>
                                <Btn onClick={() => setHl(onAir, 'pending')}>Volver a la cola</Btn>
                            </div>
                        </section>
                    ) : (
                        <p className="px-6 py-8 text-mute text-[15px] border-b border-line">Nada al aire. Elegí un mensaje de la cola o marcá uno en el feed.</p>
                    )}

                    {queue.map((h, i) => (
                        <section key={h.id} className="px-6 py-4 border-b border-line">
                            <div className="flex items-baseline gap-2 text-[15px]">
                                <span className="text-mute tabular-nums w-5">{i + 1}</span>
                                <span className="text-ink-2">{who(h.message)}</span>
                                {h.message.type === 'audio' && h.message.media_duration_s && <span className="text-mute">{mmss(h.message.media_duration_s)}</span>}
                            </div>
                            <p className="mt-1 text-[18px] leading-snug clamp-3 pl-7">{h.message.transcript ?? h.message.body ?? 'Audio sin transcripción'}</p>
                            <div className="flex gap-2 mt-3 pl-7">
                                <Btn onClick={() => setHl(h, 'on_air')} primary>Al aire ahora</Btn>
                                <Btn onClick={() => setHl(h, 'discarded')} quiet>Sacar</Btn>
                            </div>
                        </section>
                    ))}
                </div>
            </aside>
        </div>
    );
}

function Row({ m, onHighlight }) {
    const [open, setOpen] = useState(false);
    const text = m.transcript ?? m.body;
    const pending = !['ready', 'failed'].includes(m.status);
    const flagged = m.moderation?.length > 0;
    const long = (text || '').length > 220;
    const starred = !!m.highlight;

    return (
        <article className={`px-6 py-4 border-b border-line grid grid-cols-[56px_minmax(0,1fr)_auto] gap-x-4 ${flagged ? 'opacity-60' : ''}`}>
            <div className="pt-0.5">
                {m.type === 'audio' ? <Player m={m} /> : <span className="text-mute text-sm">{{ text: 'texto', image: 'foto', video: 'video', document: 'archivo' }[m.type] ?? m.type}</span>}
            </div>

            <div className="min-w-0">
                <div className="flex items-baseline gap-x-3 text-[15px] flex-wrap">
                    <span className="font-semibold">{who(m)}</span>
                    {m.contact.messages_count > 1 ? <span className="text-mute">{m.contact.messages_count} mensajes</span> : <span className="text-mute">primera vez</span>}
                    <span className="text-mute">{ago(m.sent_at)}</span>
                </div>

                {text ? (
                    <>
                        <p className={`mt-1 text-[17px] leading-snug ${open ? '' : 'clamp-3'}`}>{text}</p>
                        {long && <button onClick={() => setOpen(!open)} className="mt-1 text-sm text-mute hover:text-ink">{open ? 'Ver menos' : 'Ver completo'}</button>}
                    </>
                ) : (
                    <p className="mt-1 text-[17px] text-mute">{pending ? (STATUS_TEXT[m.status] ?? m.status) + '…' : m.status === 'failed' ? 'No se pudo procesar' : 'Sin texto'}</p>
                )}
                {m.type === 'image' && m.media_url && <a href={m.media_url} target="_blank" rel="noreferrer"><img className="mt-2 max-h-36 rounded-md" src={m.media_url} alt="" loading="lazy" /></a>}
                {m.type === 'video' && m.media_url && <video className="mt-2 max-h-64 rounded-md" controls preload="none" src={m.media_url} />}
                {m.type === 'document' && m.media_url && <a className="mt-2 inline-block text-sm text-ink-2 underline" href={m.media_url} target="_blank" rel="noreferrer">Abrir archivo</a>}

                <div className="mt-1.5 text-sm text-mute flex gap-x-3 flex-wrap">
                    {m.intent && <span className={m.intent === 'reclamo' ? 'text-ink-2' : ''}>{INTENT[m.intent]}</span>}
                    {m.topic_label && !(text || '').toLowerCase().startsWith(m.topic_label.toLowerCase().slice(0, 12)) && <span>{m.topic_label}</span>}
                    {flagged && <span className="text-air">{m.moderation.join(', ')}</span>}
                    {m.review?.length > 0 && <span className="text-ink-2">revisar: {m.review.join(', ')}</span>}
                    {m.confidence != null && m.confidence < 0.6 && <span title={`Confianza ${m.confidence}`}>poco seguro</span>}
                    {m.status === 'failed' && <span className="text-air" title={m.error}>error</span>}
                    {text && pending && <span>{STATUS_TEXT[m.status]}…</span>}
                </div>
            </div>

            <div className="flex flex-col items-end gap-2">
                {m.on_air_score >= 70 && <span className="text-sm text-ink-2 whitespace-nowrap" title={`Puntaje ${m.on_air_score}/100`}>Bueno para el aire</span>}
                {starred
                    ? <span className="text-sm text-ink-2">En cola</span>
                    : <button onClick={() => onHighlight(m)} className="px-3 py-1.5 rounded-md text-sm font-medium bg-base border border-line hover:border-ink-2">Al aire</button>}
                {m.transcript && <button onClick={() => api.feedback(m.id, true)} className="text-xs text-mute hover:text-ink" title="Marcar que la transcripción está mal">Mal transcripto</button>}
            </div>
        </article>
    );
}

function Player({ m, big }) {
    const ref = useRef(null);
    const [playing, setPlaying] = useState(false);
    const [t, setT] = useState(0);
    const dur = m.media_duration_s || 0;
    const toggle = () => { const a = ref.current; if (!a) return; playing ? a.pause() : a.play(); };
    if (!m.media_url) return <span className="text-mute text-sm">audio{dur ? ` ${mmss(dur)}` : ''}</span>;
    return (
        <div className={`flex items-center gap-2 ${big ? 'mt-3' : ''}`}>
            <audio ref={ref} src={m.media_url} preload="none"
                onPlay={() => setPlaying(true)} onPause={() => setPlaying(false)} onEnded={() => { setPlaying(false); setT(0); }}
                onTimeUpdate={(e) => setT(e.currentTarget.currentTime)} />
            <button onClick={toggle} aria-label={playing ? 'Pausar' : 'Reproducir'}
                className={`shrink-0 rounded-full grid place-items-center bg-ink text-base hover:bg-white ${big ? 'w-12 h-12' : 'w-10 h-10'}`}>
                {playing
                    ? <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor"><rect x="2" y="1" width="3.5" height="12" /><rect x="8.5" y="1" width="3.5" height="12" /></svg>
                    : <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor"><path d="M3 1.5v11l9-5.5z" /></svg>}
            </button>
            <span className={`tabular-nums ${big ? 'text-base text-ink-2' : 'text-xs text-mute'}`}>{playing ? mmss(t) : mmss(dur)}</span>
        </div>
    );
}

function Btn({ children, onClick, primary, quiet }) {
    const cls = primary ? 'bg-air text-white hover:brightness-110' : quiet ? 'text-mute hover:text-ink' : 'bg-base border border-line hover:border-ink-2';
    return <button onClick={onClick} className={`px-3.5 py-2 rounded-md text-[15px] font-medium ${cls}`}>{children}</button>;
}
