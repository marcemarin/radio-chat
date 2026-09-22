import React, { useRef, useState } from 'react';
import { mmss } from './useSala.js';

/** Botón de play con duración; `tone` = 'ink' (oscuro sobre claro) | 'paper' (claro sobre oscuro) | 'air' (rojo). */
export default function Player({ m, size = 40, tone = 'ink', showBar = false }) {
    const ref = useRef(null);
    const [playing, setPlaying] = useState(false);
    const [t, setT] = useState(0);
    const dur = m.media_duration_s || 0;
    if (!m.media_url) return <span className="text-mute text-sm">audio{dur ? ` ${mmss(dur)}` : ''}</span>;
    const toggle = () => { const a = ref.current; if (!a) return; playing ? a.pause() : a.play(); };
    const bg = tone === 'air' ? 'var(--air)' : tone === 'paper' ? 'var(--paper)' : 'var(--ink)';
    const fg = tone === 'paper' ? 'var(--night)' : '#fff';
    const pct = dur ? Math.min(100, (t / dur) * 100) : 0;
    return (
        <div className="flex items-center gap-3">
            <audio ref={ref} src={m.media_url} preload="none"
                onPlay={() => setPlaying(true)} onPause={() => setPlaying(false)} onEnded={() => { setPlaying(false); setT(0); }}
                onTimeUpdate={(e) => setT(e.currentTarget.currentTime)} />
            <button onClick={toggle} aria-label={playing ? 'Pausar' : 'Reproducir'} className="shrink-0 rounded-full grid place-items-center hover:brightness-110"
                style={{ width: size, height: size, background: bg, color: fg }}>
                {playing
                    ? <svg width={size * 0.32} height={size * 0.32} viewBox="0 0 14 14" fill="currentColor"><rect x="2" y="1" width="3.5" height="12" /><rect x="8.5" y="1" width="3.5" height="12" /></svg>
                    : <svg width={size * 0.32} height={size * 0.32} viewBox="0 0 14 14" fill="currentColor"><path d="M3 1.5v11l9-5.5z" /></svg>}
            </button>
            {showBar && <div className="h-1 rounded-full overflow-hidden" style={{ width: 260, background: 'var(--line)' }}><div className="h-full" style={{ width: `${pct}%`, background: 'var(--ink)' }} /></div>}
            <span className="tabular-nums text-sm text-mute">{playing || t > 0 ? `${mmss(t)} / ${mmss(dur)}` : mmss(dur)}</span>
        </div>
    );
}
