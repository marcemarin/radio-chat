import React, { useMemo, useState } from 'react';
import Player from './Player.jsx';
import { FILTERS, INTENT, STATUS_TEXT, ago, filterMessages, mmss, text, useSala, who } from './useSala.js';

/** Pantalla del productor: fondo papel, mensajes en serif grande, un solo rojo para "al aire". */
export default function Redaccion() {
    const { program, messages, onAir, queue, counts, topics, connected, actions } = useSala();
    const [filter, setFilter] = useState('all');
    const visible = useMemo(() => filterMessages(messages, filter), [messages, filter]);

    return (
        <div className="theme-paper h-screen grid grid-cols-[236px_minmax(0,1fr)_456px] bg-paper text-ink">
            <nav className="flex flex-col px-6 py-7 border-r border-line">
                <div className="flex flex-col gap-1 pb-5 border-b border-line">
                    <div className="font-serif text-[26px] font-medium leading-none tracking-tight">{program?.name ?? '…'}</div>
                    <div className="text-mute text-sm">{program?.station}</div>
                </div>
                <div className="flex flex-col pt-4">
                    {FILTERS.map(([k, label]) => (
                        <button key={k} onClick={() => setFilter(k)}
                            className={`flex justify-between items-baseline py-2 text-left border-b ${filter === k ? 'border-ink font-semibold text-ink' : 'border-line2 text-ink2 hover:text-ink'}`}>
                            <span>{label}</span><span className={`tabular-nums ${filter === k ? '' : 'text-mute'}`}>{counts[k] || ''}</span>
                        </button>
                    ))}
                </div>
                <div className="mt-auto flex flex-col gap-1.5 text-mute text-sm">
                    <div className="flex items-center gap-2"><span className={`w-2 h-2 rounded-full ${connected ? 'bg-good' : 'bg-air'}`} />{connected ? 'Recibiendo mensajes' : 'Reconectando'}</div>
                    <a href="/aire" className="underline decoration-line underline-offset-4 hover:text-ink">Abrir pantalla de aire</a>
                </div>
            </nav>

            <main className="flex flex-col min-w-0 px-9 overflow-hidden">
                <div className="pt-6 pb-3.5 border-b-2 border-ink flex items-baseline gap-5 flex-wrap shrink-0">
                    <span className="font-serif text-[22px] font-medium">Ahora hablan de</span>
                    {topics.length === 0 && <span className="text-mute text-[15px]">todavía nada en particular</span>}
                    {topics.map((t) => <span key={t.label} className="text-[15px]"><b>{t.label}</b> <span className="text-mute">{t.n}</span></span>)}
                </div>
                <div className="flex-1 overflow-y-auto">
                    {visible.length === 0 && <p className="text-mute py-16 text-center">Todavía no hay mensajes en este filtro. Cuando lleguen, aparecen solos.</p>}
                    {visible.map((m) => <Row key={m.id} m={m} actions={actions} />)}
                </div>
            </main>

            <aside className="border-l border-line bg-white flex flex-col min-h-0">
                <div className="px-7 pt-6 pb-4 flex items-center gap-3 border-b border-line">
                    <span className={`px-2.5 py-1 text-[13px] font-semibold tracking-[0.12em] rounded-sm ${onAir ? 'bg-air text-white' : 'bg-line2 text-mute'}`}>AL AIRE</span>
                    <span className="text-mute text-sm">{onAir ? `desde ${ago(onAir.on_air_since).replace('hace ', 'hace ')}` : 'nada al aire'}</span>
                    <span className="ml-auto text-mute text-sm">{queue.length > 0 ? `${queue.length} en cola` : ''}</span>
                </div>
                <div className="flex-1 overflow-y-auto">
                    {onAir && (
                        <section className="px-7 pt-5 pb-6 border-b border-line flex flex-col gap-3.5">
                            <div className="text-[15px] text-mute">{who(onAir.message)}</div>
                            <p className="font-serif text-[34px] leading-[1.18] font-medium tracking-tight">{text(onAir.message) ?? 'Audio sin transcripción'}</p>
                            {onAir.message.type === 'audio' && <Player m={onAir.message} size={48} tone="air" />}
                            {onAir.message.type === 'image' && onAir.message.media_url && <img className="max-h-56 rounded-md" src={onAir.message.media_url} alt="" />}
                            <div className="flex gap-2">
                                <Btn primary onClick={actions.next}>Ya salió{queue.length ? ', siguiente' : ''}</Btn>
                                <Btn onClick={() => actions.setStatus(onAir, 'pending')}>Volver a la cola</Btn>
                            </div>
                        </section>
                    )}
                    {!onAir && queue.length === 0 && <p className="px-7 py-8 text-mute text-[15px]">Marcá un mensaje con «Al aire» y aparece acá, en grande, para el conductor.</p>}
                    <section className="px-7 py-4 flex flex-col">
                        {queue.map((h, i) => (
                            <div key={h.id} className={`grid grid-cols-[24px_minmax(0,1fr)] gap-x-2.5 gap-y-1.5 py-4 ${i > 0 ? 'border-t border-line2' : ''}`}>
                                <span className="font-serif text-[22px] text-mute leading-none">{i + 1}</span>
                                <div className="flex gap-2.5 text-sm text-mute"><span className="text-ink font-medium">{who(h.message)}</span>{h.message.type === 'audio' && <span>audio {mmss(h.message.media_duration_s || 0)}</span>}</div>
                                <span />
                                <p className="font-serif text-[19px] leading-snug">{text(h.message) ?? 'Audio sin transcripción'}</p>
                                <span />
                                <div className="flex gap-2.5 mt-0.5">
                                    <Btn primary={i === 0} onClick={() => actions.setStatus(h, 'on_air')}>Al aire ahora</Btn>
                                    <Btn quiet onClick={() => actions.setStatus(h, 'discarded')}>Sacar</Btn>
                                </div>
                            </div>
                        ))}
                    </section>
                </div>
            </aside>
        </div>
    );
}

function Row({ m, actions }) {
    const [open, setOpen] = useState(false);
    const t = text(m);
    const pending = !['ready', 'failed'].includes(m.status);
    const flagged = m.moderation?.length > 0;
    const long = (t || '').length > 220;
    return (
        <article className={`grid grid-cols-[minmax(0,1fr)_140px] gap-x-6 py-5 border-b border-line ${flagged ? 'text-mute' : ''}`}>
            <div className="flex flex-col gap-2 min-w-0">
                <div className="flex gap-3 items-baseline text-sm text-mute flex-wrap">
                    <b className={`text-[15px] ${flagged ? '' : 'text-ink'}`}>{who(m)}</b>
                    <span>{m.contact.messages_count > 1 ? `${m.contact.messages_count} mensajes` : 'primera vez'}</span>
                    <span>{ago(m.sent_at)}</span>
                    {m.intent && <span className={`font-medium ${m.intent === 'reclamo' || m.intent === 'spam' ? 'text-air' : 'text-ink2'}`}>{INTENT[m.intent]}</span>}
                </div>
                {t ? (
                    <>
                        <p className={`font-serif leading-[1.3] ${flagged ? 'text-[19px]' : 'text-[23px]'} ${open ? '' : 'clamp-3'}`}>{t}</p>
                        {long && <button onClick={() => setOpen(!open)} className="self-start text-sm text-mute hover:text-ink">{open ? 'Ver menos' : 'Ver completo'}</button>}
                    </>
                ) : (
                    <p className="font-serif text-[21px] text-mute">{pending ? (STATUS_TEXT[m.status] ?? m.status) + '…' : m.status === 'failed' ? 'No se pudo procesar' : 'Sin texto'}</p>
                )}
                {m.type === 'image' && m.media_url && <a href={m.media_url} target="_blank" rel="noreferrer"><img className="max-h-36 rounded-md" src={m.media_url} alt="" loading="lazy" /></a>}
                <div className="flex items-center gap-3 text-sm text-mute flex-wrap">
                    {m.type === 'audio' && <Player m={m} size={40} tone="ink" />}
                    {m.topic_label && !(t || '').toLowerCase().startsWith(m.topic_label.toLowerCase().slice(0, 12)) && <span>{m.topic_label}</span>}
                    {t && pending && <span>{STATUS_TEXT[m.status]}…</span>}
                    {flagged && <span className="text-air">{m.moderation.join(', ')}</span>}
                    {m.review?.length > 0 && <span className="text-warn">Revisar: ¿{m.review.join(', ').replace(/_/g, ' ')}?</span>}
                    {m.status === 'failed' && <span className="text-air" title={m.error}>error</span>}
                </div>
            </div>
            <div className="flex flex-col items-end justify-between gap-2">
                <span className="text-[13px] font-medium text-good">{m.on_air_score >= 70 ? 'Bueno para el aire' : ''}</span>
                {!flagged && (m.highlight
                    ? <span className="text-sm text-mute">{m.highlight.status === 'on_air' ? 'Al aire' : m.highlight.status === 'pending' ? 'En cola' : 'Ya salió'}</span>
                    : <Btn primary={m.on_air_score >= 70} onClick={() => actions.highlight(m)}>Al aire</Btn>)}
                {m.transcript && <button onClick={() => actions.badTranscript(m)} className="text-xs text-mute hover:text-ink" title="Marcar que la transcripción está mal">Mal transcripto</button>}
            </div>
        </article>
    );
}

function Btn({ children, onClick, primary, quiet }) {
    const cls = primary ? 'bg-ink text-paper hover:opacity-90' : quiet ? 'text-mute hover:text-ink px-1.5' : 'border border-line text-ink hover:border-ink';
    return <button onClick={onClick} className={`px-4 py-2 rounded-[3px] text-[15px] font-medium ${cls}`}>{children}</button>;
}
