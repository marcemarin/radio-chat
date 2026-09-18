import React, { useEffect, useMemo, useRef, useState } from 'react';
import { api } from './api.js';
import { createEcho } from './echo.js';

const INTENTS = {
    reclamo: { label: 'Reclamo', cls: 'bg-red-500/20 text-red-300 border-red-500/40' },
    pedido_musical: { label: 'Pedido', cls: 'bg-violet-500/20 text-violet-300 border-violet-500/40' },
    saludo: { label: 'Saludo', cls: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' },
    opinion: { label: 'Opinión', cls: 'bg-sky-500/20 text-sky-300 border-sky-500/40' },
    concurso: { label: 'Concurso', cls: 'bg-amber-500/20 text-amber-300 border-amber-500/40' },
    consulta: { label: 'Consulta', cls: 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' },
    spam: { label: 'Spam', cls: 'bg-zinc-500/20 text-zinc-400 border-zinc-500/40' },
    otro: { label: 'Otro', cls: 'bg-zinc-500/20 text-zinc-300 border-zinc-500/40' },
};
const FILTERS = [['all', 'Todo'], ['audio', 'Audios'], ['reclamo', 'Reclamos'], ['pedido_musical', 'Pedidos'], ['concurso', 'Concurso'], ['saludo', 'Saludos'], ['opinion', 'Opiniones']];
const SENT = { positivo: 'bg-emerald-400', neutral: 'bg-zinc-400', negativo: 'bg-red-400' };

const fmtTime = (iso) => new Date(iso).toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
const ago = (iso) => {
    const s = Math.max(0, (Date.now() - new Date(iso)) / 1000);
    return s < 60 ? 'ahora' : s < 3600 ? `${Math.floor(s / 60)} min` : `${Math.floor(s / 3600)} h`;
};
const normTopic = (t) => (t || '').toLowerCase().replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trim();

export default function Sala() {
    const [program, setProgram] = useState(null);
    const [messages, setMessages] = useState([]);
    const [highlights, setHighlights] = useState([]);
    const [filter, setFilter] = useState('all');
    const [connected, setConnected] = useState(false);
    const [flash, setFlash] = useState(new Set());
    const echoRef = useRef(null);

    const upsert = (m, isNew = false) => {
        setMessages((prev) => {
            const i = prev.findIndex((x) => x.id === m.id);
            if (i === -1) return [m, ...prev].slice(0, 500);
            const next = prev.slice();
            next[i] = m;
            return next;
        });
        if (isNew) {
            setFlash((f) => new Set(f).add(m.id));
            setTimeout(() => setFlash((f) => { const n = new Set(f); n.delete(m.id); return n; }), 2500);
        }
    };

    const reloadHighlights = (pid) => api.highlights(pid).then((r) => setHighlights(r.highlights));

    useEffect(() => {
        let echo;
        (async () => {
            const p = await api.program();
            setProgram(p);
            const [{ messages }, { highlights }] = await Promise.all([api.messages(p.id, { limit: 200 }), api.highlights(p.id)]);
            setMessages(messages);
            setHighlights(highlights);

            echo = createEcho();
            echoRef.current = echo;
            echo.connector.pusher.connection.bind('state_change', ({ current }) => setConnected(current === 'connected'));
            echo.channel(`sala.${p.id}`)
                .listen('.message.received', ({ message }) => upsert(message, true))
                .listen('.message.updated', ({ message }) => { upsert(message); reloadHighlights(p.id); });
        })();
        return () => echo?.disconnect();
    }, []);

    const visible = useMemo(() => messages.filter((m) => {
        if (m.moderation?.includes('spam') && filter !== 'spam') return false;
        if (filter === 'all') return true;
        if (filter === 'audio') return m.type === 'audio';
        return m.intent === filter;
    }), [messages, filter]);

    const topics = useMemo(() => {
        const cutoff = Date.now() - 60 * 60 * 1000;
        const map = new Map();
        for (const m of messages) {
            if (!m.topic_label || new Date(m.sent_at) < cutoff) continue;
            const k = normTopic(m.topic_label);
            const e = map.get(k) || { label: m.topic_label, n: 0, last: m.sent_at, ids: [] };
            e.n++; e.ids.push(m.id);
            if (m.sent_at > e.last) e.last = m.sent_at;
            map.set(k, e);
        }
        return [...map.values()].sort((a, b) => b.n - a.n).slice(0, 8);
    }, [messages]);

    const onHighlight = async (m) => { await api.highlight(m.id); reloadHighlights(program.id); };
    const setHl = async (h, status) => { await api.updateHighlight(h.id, { status }); reloadHighlights(program.id); };

    return (
        <div className="h-screen grid grid-cols-[minmax(0,1fr)_420px] gap-0">
            <main className="flex flex-col min-h-0 border-r border-white/10">
                <header className="flex items-center gap-4 px-5 py-3 border-b border-white/10 bg-[#0e141b]">
                    <div>
                        <div className="text-lg font-semibold leading-tight">{program?.name ?? '…'}</div>
                        <div className="text-xs text-zinc-400">{program?.station}</div>
                    </div>
                    <div className="flex gap-1.5 ml-4 flex-wrap">
                        {FILTERS.map(([k, label]) => (
                            <button key={k} onClick={() => setFilter(k)}
                                className={`px-3 py-1 rounded-full text-sm border transition ${filter === k ? 'bg-white text-black border-white' : 'border-white/15 text-zinc-300 hover:border-white/40'}`}>
                                {label}
                            </button>
                        ))}
                    </div>
                    <div className="ml-auto flex items-center gap-2 text-xs text-zinc-400">
                        <span className={`inline-block w-2 h-2 rounded-full ${connected ? 'bg-emerald-400' : 'bg-red-400'}`} />
                        {connected ? 'en vivo' : 'reconectando…'} · {messages.length} msgs
                    </div>
                </header>

                {topics.length > 0 && (
                    <div className="flex gap-2 px-5 py-2 overflow-x-auto border-b border-white/10 bg-[#0e141b]/60">
                        {topics.map((t) => (
                            <div key={t.label} className="shrink-0 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-sm">
                                <span className="font-semibold text-white mr-1.5">{t.n}</span>
                                <span className="text-zinc-300">{t.label}</span>
                                <span className="text-zinc-500 ml-1.5 text-xs">{ago(t.last)}</span>
                            </div>
                        ))}
                    </div>
                )}

                <div className="flex-1 overflow-y-auto px-5 py-3 space-y-2">
                    {visible.length === 0 && <div className="text-zinc-500 text-sm py-10 text-center">Esperando mensajes…</div>}
                    {visible.map((m) => <Card key={m.id} m={m} flash={flash.has(m.id)} onHighlight={onHighlight} />)}
                </div>
            </main>

            <aside className="flex flex-col min-h-0 bg-[#0e141b]">
                <header className="px-5 py-3 border-b border-white/10">
                    <div className="text-lg font-semibold">Al aire</div>
                    <div className="text-xs text-zinc-400">{highlights.length} destacados</div>
                </header>
                <div className="flex-1 overflow-y-auto p-4 space-y-3">
                    {highlights.length === 0 && <div className="text-zinc-500 text-sm py-10 text-center">Marcá mensajes con «Al aire» para que aparezcan acá, en grande, para el conductor.</div>}
                    {highlights.map((h) => (
                        <div key={h.id} className={`rounded-xl p-4 border ${h.status === 'on_air' ? 'border-amber-400 bg-amber-400/10' : 'border-white/10 bg-white/5'}`}>
                            <div className="flex items-center gap-2 text-sm text-zinc-300 mb-1">
                                <span className="font-semibold text-white">{h.message.contact.name}</span>
                                {h.message.location && <span>· {h.message.location}</span>}
                                {h.message.type === 'audio' && <span className="text-xs px-1.5 rounded bg-white/10">audio {h.message.media_duration_s ?? ''}s</span>}
                            </div>
                            <div className="text-xl leading-snug">{h.message.transcript ?? h.message.body ?? '…'}</div>
                            {h.message.media_url && h.message.type === 'audio' && <audio className="w-full mt-2" controls preload="none" src={h.message.media_url} />}
                            {h.message.media_url && h.message.type === 'image' && <img className="mt-2 max-h-64 rounded-lg" src={h.message.media_url} alt="" />}
                            <div className="flex gap-2 mt-3">
                                {h.status !== 'on_air' && <Btn onClick={() => setHl(h, 'on_air')} accent>Al aire ahora</Btn>}
                                <Btn onClick={() => setHl(h, 'done')}>Leído</Btn>
                                <Btn onClick={() => setHl(h, 'discarded')} subtle>Descartar</Btn>
                            </div>
                        </div>
                    ))}
                </div>
            </aside>
        </div>
    );
}

function Card({ m, flash, onHighlight }) {
    const intent = INTENTS[m.intent] ?? null;
    const text = m.transcript ?? m.body;
    const pending = !['ready', 'failed'].includes(m.status);
    const flagged = m.moderation?.length > 0;
    return (
        <article className={`rounded-xl border p-3.5 transition-colors ${flash ? 'border-emerald-400/60 bg-emerald-400/5' : 'border-white/10 bg-white/[0.03]'} ${flagged ? 'opacity-70' : ''}`}>
            <div className="flex items-center gap-2 text-sm">
                <span className="font-semibold">{m.contact.name}</span>
                <span className="text-zinc-500">{m.contact.messages_count > 1 ? `· ${m.contact.messages_count} msgs` : '· nuevo'}</span>
                {m.location && <span className="text-zinc-300">· {m.location}</span>}
                <span className="text-zinc-500 ml-auto" title={fmtTime(m.sent_at)}>{ago(m.sent_at)}</span>
            </div>
            <div className="mt-1.5 text-[15px] leading-snug">
                {text ?? <span className="text-zinc-500 italic">{m.type === 'audio' ? 'audio' : m.type}{m.media_duration_s ? ` · ${m.media_duration_s}s` : ''}</span>}
                {pending && <span className="ml-2 text-xs text-zinc-500 animate-pulse">{{ received: 'recibido', media: 'descargando', transcribing: 'transcribiendo…', classifying: 'clasificando…' }[m.status]}</span>}
                {m.status === 'failed' && <span className="ml-2 text-xs text-red-400" title={m.error}>error</span>}
            </div>
            {m.media_url && m.type === 'audio' && <audio className="w-full mt-2" controls preload="none" src={m.media_url} />}
            {m.media_url && m.type === 'image' && <a href={m.media_url} target="_blank" rel="noreferrer"><img className="mt-2 max-h-56 rounded-lg border border-white/10" src={m.media_url} alt="" loading="lazy" /></a>}
            {m.media_url && m.type === 'video' && <video className="w-full mt-2 max-h-72 rounded-lg" controls preload="none" src={m.media_url} />}
            {m.media_url && m.type === 'document' && <a className="inline-block mt-2 text-sm text-sky-300 underline" href={m.media_url} target="_blank" rel="noreferrer">Abrir documento</a>}
            <div className="flex items-center gap-2 mt-2.5 text-xs">
                {intent && <span className={`px-2 py-0.5 rounded-full border ${intent.cls}`}>{intent.label}</span>}
                {m.sentiment && <span className={`inline-block w-2 h-2 rounded-full ${SENT[m.sentiment]}`} title={m.sentiment} />}
                {m.on_air_score != null && <span className="text-zinc-400">aire {m.on_air_score}</span>}
                {m.topic_label && <span className="text-zinc-500 truncate">· {m.topic_label}</span>}
                {flagged && <span className="text-red-400">· {m.moderation.join(', ')}</span>}
                <span className="ml-auto flex gap-1.5">
                    {m.transcript && <button onClick={() => api.feedback(m.id, true)} className="text-zinc-500 hover:text-zinc-300" title="Marcar transcripción mala">✗ transcripción</button>}
                    {m.highlight
                        ? <span className="px-2 py-0.5 rounded bg-amber-400/20 text-amber-300">★ al aire</span>
                        : <button onClick={() => onHighlight(m)} className="px-2 py-0.5 rounded bg-white/10 hover:bg-white/20">★ Al aire</button>}
                </span>
            </div>
        </article>
    );
}

function Btn({ children, onClick, accent, subtle }) {
    const cls = accent ? 'bg-amber-400 text-black hover:bg-amber-300' : subtle ? 'text-zinc-400 hover:text-white' : 'bg-white/10 hover:bg-white/20';
    return <button onClick={onClick} className={`px-3 py-1.5 rounded-lg text-sm font-medium ${cls}`}>{children}</button>;
}
