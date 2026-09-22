import React, { useEffect, useMemo, useState } from 'react';
import Player from './Player.jsx';
import { INTENT, ago, filterMessages, mmss, text, useSala, who } from './useSala.js';

const CHIPS = [['best', 'Mejores'], ['audio', 'Audios'], ['reclamo', 'Reclamos'], ['pedido_musical', 'Pedidos'], ['all', 'Todo']];

/** Pantalla del conductor: lo que está al aire ocupa la pantalla; el feed es una columna con lo mejor. */
export default function Aire() {
    const { program, messages, onAir, queue, counts, topics, connected, actions } = useSala();
    const [filter, setFilter] = useState('best');
    const [clock, setClock] = useState(new Date());
    useEffect(() => { const t = setInterval(() => setClock(new Date()), 1000); return () => clearInterval(t); }, []);

    const recent = useMemo(() => {
        const cutoff = Date.now() - 15 * 60_000;
        return filterMessages(messages, filter).filter((m) => filter !== 'best' || new Date(m.sent_at) >= cutoff).slice(0, 12);
    }, [messages, filter]);

    const nextUp = queue[0];
    const m = onAir?.message;

    return (
        <div className="theme-night h-screen grid grid-cols-[minmax(0,1fr)_420px] bg-night text-paper">
            <main className="grid grid-rows-[72px_minmax(0,1fr)_auto] px-14 pb-10 min-w-0">
                <header className="flex items-center gap-4 border-b border-line">
                    <span className={`w-4 h-4 rounded-full ${onAir ? 'bg-air lamp' : 'bg-line'}`} />
                    <span className="font-bold tracking-[0.24em] text-[15px]">AL AIRE</span>
                    <span className="text-mute truncate">{m ? `${who(m)}${m.type === 'audio' ? ` · audio ${mmss(m.media_duration_s || 0)}` : ''} · ${ago(m.sent_at)}` : 'nada al aire'}</span>
                    <span className="ml-auto text-mute tabular-nums">{clock.toLocaleTimeString('es-AR')}</span>
                </header>

                <section className="flex flex-col justify-center gap-7 py-6 min-h-0">
                    {m ? (
                        <>
                            <p className="font-serif text-[clamp(40px,4.4vw,64px)] leading-[1.1] tracking-tight max-w-[18ch] text-balance overflow-hidden">{text(m) ?? 'Audio sin transcripción'}</p>
                            {m.type === 'audio' && <Player m={m} size={64} tone="paper" showBar />}
                            {m.type === 'image' && m.media_url && <img className="max-h-72 rounded-md self-start" src={m.media_url} alt="" />}
                        </>
                    ) : (
                        <p className="text-mute text-2xl max-w-[30ch]">{queue.length ? 'Hay mensajes en cola. Tocá «Siguiente» cuando quieras arrancar.' : 'Cuando producción marque un mensaje, aparece acá en grande.'}</p>
                    )}
                </section>

                <footer className="flex items-center gap-3">
                    <button onClick={actions.next} disabled={!onAir && !queue.length}
                        className="px-6 py-3.5 rounded-lg bg-paper text-night text-[17px] font-semibold hover:opacity-90 disabled:opacity-40 whitespace-nowrap">
                        {onAir ? (queue.length ? 'Ya salió, siguiente' : 'Ya salió') : 'Siguiente'}
                    </button>
                    {onAir && <button onClick={() => actions.setStatus(onAir, 'pending')} className="px-5 py-3.5 rounded-lg border border-line2 text-[17px] font-medium hover:border-paper whitespace-nowrap">Volver a la cola</button>}
                    <span className="ml-auto text-mute text-[15px] truncate">
                        {nextUp ? <>Siguiente: <span className="text-paper">{who(nextUp.message)} · {(text(nextUp.message) || 'audio').slice(0, 60)}</span></> : 'Nada más en cola'}
                    </span>
                </footer>
            </main>

            <aside className="border-l border-line bg-panel grid grid-rows-[auto_minmax(0,1fr)] min-h-0">
                <div className="px-6 pt-5 pb-3.5 flex flex-col gap-3 border-b border-line">
                    <div className="flex items-baseline justify-between"><span className="font-semibold text-[17px]">{program?.name ?? '…'}</span><span className="text-mute text-sm">{counts.all} mensajes · <span className={connected ? 'text-good' : 'text-air'}>{connected ? 'en vivo' : 'reconectando'}</span></span></div>
                    <div className="flex gap-1.5 flex-wrap">
                        {CHIPS.map(([k, label]) => (
                            <button key={k} onClick={() => setFilter(k)} className={`px-3 py-1.5 rounded-full text-sm ${filter === k ? 'bg-paper text-night font-medium' : 'border border-line2 text-ink2 hover:border-paper'}`}>
                                {label}{k !== 'best' && k !== 'all' && counts[k] ? ` ${counts[k]}` : ''}
                            </button>
                        ))}
                    </div>
                    {topics.length > 0 && <div className="text-mute text-sm">Ahora hablan de {topics.map((t, i) => <span key={t.label}><span className="text-ink2">{t.label}</span>{i < topics.length - 1 ? ', ' : ''}</span>)}</div>}
                </div>

                <div className="flex flex-col overflow-y-auto">
                    {queue.length > 0 && <div className="px-6 pt-3 pb-2 text-[13px] tracking-[0.1em] text-mute">EN COLA</div>}
                    {queue.map((h, i) => (
                        <article key={h.id} className={`grid grid-cols-[20px_minmax(0,1fr)_auto] gap-x-2.5 gap-y-1 px-6 pt-2.5 pb-3.5 border-b border-line ${i === 0 ? 'bg-panel2' : ''}`}>
                            <span className="text-mute text-[15px]">{i + 1}</span>
                            <div className="flex gap-2 text-sm text-mute min-w-0"><span className="text-paper font-medium truncate">{who(h.message)}</span>{h.message.type === 'audio' && <span>{mmss(h.message.media_duration_s || 0)}</span>}<span>{INTENT[h.message.intent] ?? ''}</span></div>
                            <button onClick={() => actions.setStatus(h, 'on_air')} className={`px-2.5 py-1 rounded-md text-[13px] font-semibold ${i === 0 ? 'bg-air text-white' : 'border border-line2 text-paper'}`}>Al aire</button>
                            <span />
                            <p className="col-span-2 text-[16px] leading-snug clamp-3">{text(h.message) ?? 'Audio sin transcripción'}</p>
                        </article>
                    ))}
                    <div className="px-6 pt-4 pb-2 text-[13px] tracking-[0.1em] text-mute">{filter === 'best' ? 'MEJORES DE LOS ÚLTIMOS 15 MIN' : 'MENSAJES'}</div>
                    {recent.length === 0 && <p className="px-6 py-6 text-mute text-sm">Nada por acá todavía.</p>}
                    {recent.map((r) => (
                        <article key={r.id} className="grid grid-cols-[40px_minmax(0,1fr)_auto] gap-x-3 gap-y-1 px-6 py-2.5 border-b border-line items-start">
                            {r.type === 'audio' ? <Player m={r} size={40} tone="paper" /> : <span className="text-mute text-[13px] pt-3">texto</span>}
                            <div className="flex flex-col gap-0.5 min-w-0">
                                <div className="flex gap-2 text-sm text-mute"><span className="text-paper font-medium truncate">{who(r)}</span><span>{INTENT[r.intent] ?? ''}</span></div>
                                <p className="text-[15px] leading-snug text-ink2 clamp-3">{text(r) ?? '…'}</p>
                            </div>
                            {r.highlight
                                ? <span className="text-[13px] text-mute pt-1">{r.highlight.status === 'pending' ? 'En cola' : 'Salió'}</span>
                                : <button onClick={() => actions.highlight(r)} className="px-2.5 py-1 rounded-md text-[13px] font-medium border border-line2 text-paper hover:border-paper">A la cola</button>}
                        </article>
                    ))}
                </div>
            </aside>
        </div>
    );
}
