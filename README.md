# Radio Chat — sala de producción para mensajes de oyentes

Recibe los WhatsApp de la audiencia de un programa de radio/TV, transcribe los audios, clasifica (intención, sentimiento, lugar, "vale para el aire"), agrupa por tema y muestra todo en una Sala en vivo con una pantalla "Al aire" para el conductor.

Diseño y plan del piloto: `../piloto-fm-street.md`.

## Stack
Laravel 13 / PHP 8.4 · Horizon (colas Redis) · Reverb (websockets) · Postgres 17 + pgvector · React 19 + Vite + Tailwind 4 · Evolution API (WhatsApp por QR, para la demo) · STT: AssemblyAI / Deepgram / OpenAI · Clasificación: Anthropic (Claude Haiku).

## Correr en local
```bash
docker compose up -d                 # postgres :5434, redis :6390, evolution :8101
cp .env.example .env && php artisan key:generate   # (ya hecho en este clon)
composer install && npm install
php artisan migrate
php artisan radio:setup "Demo FM Street" --station="FM Street 101.5 Posadas" --context="..."

php artisan serve --host=0.0.0.0 --port=8100   # API + Sala  → http://localhost:8100
php artisan horizon                             # workers     → http://localhost:8100/horizon
php artisan reverb:start --port=8102            # websockets
npm run dev                                     # (o `npm run build` una vez)
```

## Conectar WhatsApp (demo por QR)
```bash
php artisan wa:connect --open   # crea la instancia en Evolution y abre el QR
php artisan wa:status
```
Evolution manda los webhooks a `EVOLUTION_WEBHOOK_URL` (`host.docker.internal:8100` desde el contenedor).

## Probar sin WhatsApp
```bash
php artisan radio:fake-burst 20 --audio-dir=storage/app/demo-audios
```

## Pipeline por mensaje
`webhook → IngestMessageJob (ingest) → DownloadMediaJob (media) → TranscribeMessageJob (stt) → ClassifyMessageJob (classify)`; cada paso emite `message.updated` por Reverb al canal `sala.{program_id}`.

## Drivers
- `STT_DRIVER`: `assemblyai` | `deepgram` | `openai` | `fake`. Benchmark: `php artisan stt:benchmark ./audios`.
- `CLASSIFY_DRIVER`: `anthropic` (necesita `ANTHROPIC_API_KEY`) | `fake` (heurístico).

## Pendiente (ver plan §3/§6)
Clustering de temas con embeddings (hoy la Sala agrupa por `topic_label` en el cliente) · pantalla Audiencia · adapter Meta Cloud API para el piloto · auth y canales privados · retención de media · particionado de `messages`.
