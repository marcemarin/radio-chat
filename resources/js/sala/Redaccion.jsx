import React, { useEffect, useMemo, useRef, useState } from 'react';
import Player from './Player.jsx';
import { FILTERS, INTENT, STATUS_TEXT, ago, filterMessages, mmss, text, useSala, who } from './useSala.js';

/**
 * Pantalla del productor como panel de intercom de estudio: cada mensaje es una tecla
 * retroiluminada que se aprieta para mandarlo al conductor; cada estado es un tally.
 * Apagada = nuevo · verde = listo para el aire · ámbar = en cola · rojo = al aire · tenue = spam.
 */
export default function Redaccion() {
    const { program, messages, onAir, queue, counts, topics, connected, actions } = useSala();
    const [filter, setFilter] = useState('all');
    const [queueOpen, setQueueOpen] = useState(false);
    const visible = useMemo(() => filterMessages(messages, filter), [messages, filter]);

    return (
        <div className="theme-panel h-screen grid grid-rows-[auto_minmax(0,1fr)] bg-panel text-ink">
            <header className="grid grid-cols-[auto_minmax(0,1fr)_auto] max-md:grid-cols-[1fr_auto] items-center gap-x-6 gap-y-2 px-5 py-2 md:h-14 md:py-0 border-b border-edge bg-panel2">
                <div className="strip px-3 py-1.5 min-w-[200px]">
                    <div className="text-[13px] font-semibold tracking-[0.06em] uppercase truncate">{program?.name ?? '…'}</div>
                    <div className="text-[11px] text-mute tracking-[0.04em] truncate">{program?.station ?? ''}</div>
                </div>
                <nav className="flex gap-1.5 overflow-x-auto no-bar max-md:order-last max-md:col-span-2" aria-label="Canales">
                    {FILTERS.map(([k, label]) => (
                        <button key={k} onClick={() => setFilter(k)} aria-pressed={filter === k}
                            className={`key key-sm flex items-center gap-2 px-3 h-9 shrink-0 ${filter === k ? 'key-lit' : ''}`}>
                            <span className="text-[13px] font-medium tracking-[0.02em]">{label}</span>
                            <span className="led-digits tabular-nums text-[13px]">{counts[k] ?? 0}</span>
                        </button>
                    ))}
                </nav>
                <div className="flex items-center gap-3 text-[13px] text-mute whitespace-nowrap max-md:justify-self-end">
                    <span className={`lamp ${connected ? 'lamp-green' : 'lamp-red'}`} aria-hidden="true" />
                    <span>{connected ? 'En vivo' : 'Sin conexión'}</span>
                    <span className="led-digits tabular-nums">{counts.all}</span>
                    <a href="/aire" className="ml-2 key key-sm px-3 h-9 flex items-center text-[13px] font-medium max-md:hidden">Pantalla de aire</a>
                </div>
            </header>

            <div className="grid grid-cols-[minmax(0,1fr)_400px] min-h-0 max-md:grid-cols-1 max-md:grid-rows-[auto_minmax(0,1fr)]">
                <main className="flex flex-col min-w-0 min-h-0">
                    <div className="px-5 h-10 flex items-center gap-4 border-b border-edge text-[13px] text-mute overflow-x-auto no-bar shrink-0">
                        <span className="tracking-[0.06em] uppercase text-[11px] font-semibold">Ahora hablan de</span>
                        {topics.length === 0 && <span>todavía nada en particular</span>}
                        {topics.map((t) => <span key={t.label} className="whitespace-nowrap"><span className="text-ink font-medium">{t.label}</span> <span className="led-digits tabular-nums">{t.n}</span></span>)}
                    </div>
                    <div className="flex-1 overflow-y-auto">
                        {visible.length === 0 && (
                            <div className="px-8 py-20 max-w-md text-mute">
                                <p className="text-ink text-[17px] font-medium mb-2">Cuando lleguen mensajes, se encienden acá.</p>
                                <p className="text-[15px] leading-relaxed">Cada mensaje es una tecla. Verde: bueno para el aire. Apretá <b className="text-ink">A la cola</b> para mandárselo al conductor; se pone ámbar. El que está al aire, rojo.</p>
                            </div>
                        )}
                        {visible.map((m) => <Row key={m.id} m={m} actions={actions} />)}
                    </div>
                </main>

                <aside className="border-l border-edge bg-panel2 grid grid-rows-[auto_minmax(0,1fr)] min-h-0 max-md:order-first max-md:border-l-0 max-md:border-b max-md:max-h-[50vh]">
                    <section className={`px-5 pt-4 pb-5 border-b border-edge flex flex-col gap-3 ${onAir ? 'onair-bg' : ''}`}>
                        <div className="flex items-center gap-3">
                            <span className={`lamp lamp-lg ${onAir ? 'lamp-red lamp-glow' : ''}`} aria-hidden="true" />
                            <span className="text-[12px] font-semibold tracking-[0.14em] uppercase">Al aire</span>
                            <span className="ml-auto text-[13px] text-mute">{onAir ? `desde ${ago(onAir.on_air_since)}` : 'nada al aire'}</span>
                        </div>
                        {onAir ? (
                            <>
                                <div className="strip px-3 py-1.5 self-start text-[12px] tracking-[0.04em]">{who(onAir.message)}</div>
                                <p className="text-[22px] max-md:text-[17px] leading-[1.25] font-medium min-w-0 break-words">{text(onAir.message) ?? 'Audio sin transcripción'}</p>
                                {onAir.message.type === 'audio' && <Player m={onAir.message} size={44} tone="air" />}
                                {onAir.message.type === 'image' && onAir.message.media_url && <img className="max-h-48 rounded-md self-start" src={onAir.message.media_url} alt="" />}
                                <div className="flex gap-2 pt-1">
                                    <button onClick={actions.next} className="key key-red px-4 h-11 text-[14px] font-semibold">Ya salió{queue.length ? ', siguiente' : ''}</button>
                                    <button onClick={() => actions.setStatus(onAir, 'pending')} className="key px-4 h-11 text-[14px] font-medium">Volver a la cola</button>
                                </div>
                            </>
                        ) : queue.length > 0 ? (
                            <button onClick={actions.next} className="key key-red self-start px-4 h-11 text-[14px] font-semibold">Poner al aire el primero de la cola</button>
                        ) : (
                            <p className="text-[14px] text-mute leading-relaxed">Lo que mandes a la cola aparece acá cuando salga al aire; el conductor lo ve en grande en su pantalla.</p>
                        )}
                    </section>

                    <section className={`overflow-y-auto min-h-0 ${queueOpen ? '' : 'max-md:overflow-hidden'}`}>
                        <div className="max-md:hidden px-5 h-10 flex items-center justify-between text-[11px] font-semibold tracking-[0.14em] uppercase text-mute border-b border-edge">
                            <span>Al conductor</span><span className="led-digits tabular-nums normal-case tracking-normal text-[13px]">{queue.length}</span>
                        </div>
                        <button onClick={() => setQueueOpen(!queueOpen)} aria-expanded={queueOpen} className="md:hidden w-full px-5 h-10 flex items-center justify-between text-[11px] font-semibold tracking-[0.14em] uppercase text-mute border-b border-edge">
                            <span>Al conductor</span>
                            <span className="flex items-center gap-2"><span className="led-digits tabular-nums normal-case tracking-normal text-[13px]">{queue.length}</span><span className="normal-case tracking-normal text-[12px]">{queueOpen ? 'Ocultar' : 'Ver cola'}</span></span>
                        </button>
                        {queue.length === 0 && <p className="px-5 py-6 text-[14px] text-mute">La cola está vacía.</p>}
                        {queue.map((h, i) => (
                            <article key={h.id} className={`grid grid-cols-[44px_minmax(0,1fr)] gap-x-3 gap-y-1.5 px-5 py-3.5 border-b border-edge ${queueOpen ? '' : 'max-md:hidden'}`}>
                                <button onClick={() => actions.setStatus(h, 'on_air')} aria-label={`Poner al aire: ${who(h.message)}`}
                                    className="key key-amber h-11 w-11 grid place-items-center led-digits tabular-nums text-[15px] font-semibold">{i + 1}</button>
                                <div className="min-w-0 flex flex-col gap-1">
                                    <div className="flex gap-2 text-[12px] text-mute min-w-0"><span className="text-ink font-medium truncate">{who(h.message)}</span>{h.message.type === 'audio' && <span className="tabular-nums">{mmss(h.message.media_duration_s || 0)}</span>}</div>
                                    <p className="text-[15px] leading-snug clamp-3">{text(h.message) ?? 'Audio sin transcripción'}</p>
                                </div>
                                <span />
                                <div className="flex items-center gap-3 text-[12px] text-mute">
                                    <span>Tecla {i + 1}: al aire</span>
                                    <button onClick={() => actions.setStatus(h, 'discarded')} className="h-8 px-2 text-[13px] text-mute hover:text-ink">Sacar</button>
                                </div>
                            </article>
                        ))}
                    </section>
                </aside>
            </div>
        </div>
    );
}

function Row({ m, actions }) {
    const [open, setOpen] = useState(false);
    const [pressed, setPressed] = useState(false);
    const [arrived, setArrived] = useState(() => Date.now() - new Date(m.sent_at) < 20_000);
    useEffect(() => { if (!arrived) return; const t = setTimeout(() => setArrived(false), 2000); return () => clearTimeout(t); }, [arrived]);

    const t = text(m);
    const pending = !['ready', 'failed'].includes(m.status);
    const spam = m.moderation?.includes('spam');
    const flagged = m.moderation?.length > 0;
    const long = (t || '').length > 240;
    const hl = m.highlight?.status;
    const state = hl === 'on_air' ? 'air' : hl === 'pending' ? 'queued' : hl === 'done' ? 'done' : spam ? 'spam' : pending ? 'new' : (m.on_air_score ?? 0) >= 60 && !flagged ? 'ready' : 'idle';
    const stateText = { air: 'al aire', queued: 'en cola', done: 'salió', spam: 'spam', new: (STATUS_TEXT[m.status] ?? m.status) + '…', ready: 'listo', idle: '' }[state];

    const queueIt = async () => { setPressed(true); setTimeout(() => setPressed(false), 220); await actions.highlight(m); };

    return (
        <article className={`grid grid-cols-[56px_minmax(0,1fr)_auto] gap-x-4 px-5 py-3.5 border-b border-edge ${spam ? 'opacity-45' : ''} ${arrived ? 'row-arrived' : ''}`}>
            <div className="flex flex-col items-center gap-1.5">
                <div className={`key key-state ${state === 'air' ? 'key-red' : state === 'queued' ? 'key-amber' : ''}`} aria-hidden="true" title={m.on_air_score != null ? `Puntaje para el aire ${m.on_air_score}/100` : ''}>
                    <span className="key-fill" style={{ transform: `scaleY(${state === 'ready' || state === 'idle' ? (m.on_air_score ?? 0) / 100 : 0})` }} />
                    <span className={`lamp lamp-sm ${state === 'ready' ? 'lamp-green' : state === 'air' ? 'lamp-red' : state === 'queued' ? 'lamp-amber' : ''}`} />
                </div>
                <span className="text-[11px] tracking-[0.06em] uppercase text-mute text-center leading-tight min-h-[1.2em]">{stateText}</span>
            </div>

            <div className="min-w-0 flex flex-col gap-1.5">
                <div className="flex items-center gap-2 min-w-0">
                    <span className="strip px-2.5 py-1 text-[12px] tracking-[0.04em] truncate min-w-[6rem]">{who(m)}</span>
                    <span className="text-[12px] text-mute whitespace-nowrap shrink-0 max-md:hidden">{m.contact.messages_count > 1 ? `${m.contact.messages_count} mensajes` : 'primera vez'}</span>
                    <span className="text-[12px] text-mute whitespace-nowrap shrink-0">{ago(m.sent_at)}</span>
                </div>
                {t ? (
                    <>
                        <p className={`text-[16px] leading-[1.4] ${open ? '' : 'clamp-3'}`}>{t}</p>
                        {long && <button onClick={() => setOpen(!open)} className="self-start text-[12px] text-mute hover:text-ink">{open ? 'Ver menos' : 'Ver completo'}</button>}
                    </>
                ) : (
                    <p className="text-[16px] text-mute">{pending ? '' : m.status === 'failed' ? 'No se pudo procesar este mensaje.' : 'Sin texto'}</p>
                )}
                {m.type === 'image' && m.media_url && <a href={m.media_url} target="_blank" rel="noreferrer" className="self-start"><img className="max-h-32 rounded-md" src={m.media_url} alt="" loading="lazy" /></a>}
                <div className="flex items-center gap-3 flex-wrap min-h-[28px]">
                    {m.type === 'audio' && <Player m={m} size={32} tone="paper" />}
                    {m.intent && <Label>{INTENT[m.intent]}</Label>}
                    {m.topic_label && !(t || '').toLowerCase().startsWith(m.topic_label.toLowerCase().slice(0, 12)) && <Label>{m.topic_label}</Label>}
                    {flagged && !spam && <Label tone="red">{m.moderation.join(', ')}</Label>}
                    {m.review?.length > 0 && <Label tone="amber">revisar: {m.review.join(', ').replace(/_/g, ' ')}</Label>}
                    {m.status === 'failed' && <Label tone="red">error</Label>}
                </div>
            </div>

            <div className="flex flex-col items-end justify-between gap-2 pl-2">
                {state === 'queued' || state === 'air' || state === 'done'
                    ? <span className="h-11 flex items-center text-[13px] text-mute">{stateText}</span>
                    : <button onClick={queueIt} disabled={spam} className={`key key-action h-11 px-4 text-[14px] font-semibold whitespace-nowrap ${pressed ? 'key-pressed' : ''}`}>A la cola</button>}
                {m.transcript && <button onClick={() => actions.badTranscript(m)} className="text-[11px] text-mute hover:text-ink whitespace-nowrap" title="Marcar que la transcripción está mal">Mal transcripto</button>}
            </div>
        </article>
    );
}

function Label({ children, tone }) {
    const cls = tone === 'red' ? 'label-red' : tone === 'amber' ? 'label-amber' : '';
    return <span className={`label ${cls}`}>{children}</span>;
}
