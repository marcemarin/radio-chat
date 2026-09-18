<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · Conectar WhatsApp</title>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#0b0f14; color:#e6edf3; font:16px system-ui, sans-serif; }
        .box { text-align:center; }
        img { width:320px; height:320px; background:#fff; border-radius:12px; padding:12px; box-sizing:border-box; }
        .state { margin-top:16px; font-size:18px; }
        .ok { color:#34d399; font-size:28px; font-weight:600; }
        .muted { color:#8b949e; font-size:14px; margin-top:8px; }
    </style>
</head>
<body>
<div class="box">
    <h1 style="font-weight:600;font-size:22px;margin:0 0 16px">Conectar el WhatsApp del programa</h1>
    <div id="qr"><img id="img" alt="QR" hidden></div>
    <div class="state" id="state">Cargando…</div>
    <div class="muted">WhatsApp → Dispositivos vinculados → Vincular un dispositivo. El código se renueva solo.</div>
</div>
<script>
    const img = document.getElementById('img'), state = document.getElementById('state');
    async function tick() {
        try {
            const r = await fetch('/wa/qr.json', { cache: 'no-store' }).then(r => r.json());
            if (r.state === 'open') { img.hidden = true; state.innerHTML = '<span class="ok">✓ Conectado</span>'; return; }
            if (r.qr) { img.src = r.qr; img.hidden = false; state.textContent = 'Escaneá el código (' + r.state + ')'; }
            else { state.textContent = r.error ? 'Error: ' + r.error : 'Esperando QR… (' + r.state + ')'; }
        } catch (e) { state.textContent = 'Sin conexión con el servidor'; }
        setTimeout(tick, 5000);
    }
    tick();
</script>
</body>
</html>
