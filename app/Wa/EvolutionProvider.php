<?php

namespace App\Wa;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvolutionProvider implements WaProvider
{
    private const EVENTS = ['MESSAGES_UPSERT', 'CONNECTION_UPDATE', 'QRCODE_UPDATED'];

    public function __construct(
        private readonly string $url,
        private readonly string $key,
        private readonly string $instance,
        private readonly ?string $webhookUrl,
    ) {}

    public static function fromConfig(): self
    {
        $c = config('services.evolution');

        return new self(rtrim($c['url'], '/'), (string) $c['key'], $c['instance'], $c['webhook_url']);
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl($this->url)
            ->withHeaders(['apikey' => $this->key])
            ->acceptJson()
            ->timeout(60);
    }

    public function connect(): array
    {
        $exists = $this->http()->get('/instance/fetchInstances', ['instanceName' => $this->instance]);
        $found = $exists->ok() && collect($exists->json())->contains(
            fn ($i) => ($i['name'] ?? $i['instance']['instanceName'] ?? null) === $this->instance
        );

        if (! $found) {
            $res = $this->http()->post('/instance/create', [
                'instanceName' => $this->instance,
                'integration' => 'WHATSAPP-BAILEYS',
                'qrcode' => true,
                'webhook' => $this->webhookConfig(),
            ]);
            $res->throw();
            $qr = $res->json('qrcode.base64');
            if ($qr) {
                return ['state' => 'connecting', 'qr' => $qr];
            }
        } else {
            $this->http()->post("/webhook/set/{$this->instance}", ['webhook' => $this->webhookConfig() + ['enabled' => true]]);
        }

        $state = $this->state();
        if ($state === 'open') {
            return ['state' => 'open', 'qr' => null];
        }

        $res = $this->http()->get("/instance/connect/{$this->instance}");
        $res->throw();

        return ['state' => 'connecting', 'qr' => $res->json('base64')];
    }

    private function webhookConfig(): array
    {
        return [
            'url' => $this->webhookUrl,
            'byEvents' => false,
            'base64' => false,
            'events' => self::EVENTS,
        ];
    }

    public function state(): string
    {
        $res = $this->http()->get("/instance/connectionState/{$this->instance}");

        return $res->ok() ? (string) ($res->json('instance.state') ?? 'unknown') : 'unknown';
    }

    public function downloadMedia(string $waMessageId, array $raw = []): array
    {
        // Con key+message Evolution descarga directo del CDN de WhatsApp; con solo el id necesita tenerlo en su DB.
        $message = isset($raw['message'], $raw['key']) ? ['key' => $raw['key'], 'message' => $raw['message']] : ['key' => ['id' => $waMessageId]];
        $res = $this->http()->post("/chat/getBase64FromMediaMessage/{$this->instance}", [
            'message' => $message,
            'convertToMp4' => false,
        ]);
        $res->throw();

        $b64 = $res->json('base64');
        if (! $b64) {
            throw new RuntimeException("Evolution no devolvió media para {$waMessageId}");
        }

        $mime = (string) ($res->json('mimetype') ?? 'application/octet-stream');
        $mime = explode(';', $mime)[0];

        return [
            'bytes' => base64_decode($b64),
            'mime' => $mime,
            'ext' => self::extFor($mime, (string) $res->json('fileName')),
        ];
    }

    public static function extFor(string $mime, string $fileName = ''): string
    {
        return match ($mime) {
            'audio/ogg' => 'ogg',
            'audio/mpeg' => 'mp3',
            'audio/mp4', 'audio/m4a' => 'm4a',
            'audio/aac' => 'aac',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'application/pdf' => 'pdf',
            default => pathinfo($fileName, PATHINFO_EXTENSION) ?: 'bin',
        };
    }

    /** Normaliza el payload de `messages.upsert` de Evolution v2. Devuelve null si hay que ignorarlo. */
    public static function parseUpsert(array $data): ?InboundMessage
    {
        $key = $data['key'] ?? [];
        if (($key['fromMe'] ?? false) || empty($key['id'])) {
            return null;
        }

        $jid = (string) ($key['remoteJid'] ?? '');
        if (str_ends_with($jid, '@g.us') || str_ends_with($jid, '@broadcast')) {
            return null; // grupos y listas: fuera del alcance de la demo
        }
        if (str_ends_with($jid, '@lid')) {
            $jid = (string) ($key['senderPn'] ?? $key['remoteJidAlt'] ?? $jid);
        }
        $phone = preg_replace('/\D+/', '', explode('@', $jid)[0]);
        if ($phone === '') {
            return null;
        }

        $m = $data['message'] ?? [];
        [$type, $body, $mime, $dur] = match (true) {
            isset($m['conversation']) => ['text', $m['conversation'], null, null],
            isset($m['extendedTextMessage']) => ['text', $m['extendedTextMessage']['text'] ?? null, null, null],
            isset($m['audioMessage']) => ['audio', null, explode(';', $m['audioMessage']['mimetype'] ?? 'audio/ogg')[0], (int) ($m['audioMessage']['seconds'] ?? 0) ?: null],
            isset($m['imageMessage']) => ['image', $m['imageMessage']['caption'] ?? null, explode(';', $m['imageMessage']['mimetype'] ?? 'image/jpeg')[0], null],
            isset($m['videoMessage']) => ['video', $m['videoMessage']['caption'] ?? null, explode(';', $m['videoMessage']['mimetype'] ?? 'video/mp4')[0], (int) ($m['videoMessage']['seconds'] ?? 0) ?: null],
            isset($m['documentMessage']) => ['document', $m['documentMessage']['caption'] ?? $m['documentMessage']['fileName'] ?? null, explode(';', $m['documentMessage']['mimetype'] ?? 'application/octet-stream')[0], null],
            isset($m['stickerMessage']) => ['sticker', null, null, null],
            default => ['other', null, null, null],
        };

        $ts = (int) ($data['messageTimestamp'] ?? time());

        return new InboundMessage(
            provider: 'evolution',
            waMessageId: (string) $key['id'],
            phone: $phone,
            pushName: $data['pushName'] ?? null,
            type: $type,
            body: $body,
            mediaMime: $mime,
            mediaDurationS: $dur,
            sentAt: \Carbon\CarbonImmutable::createFromTimestamp($ts),
            raw: ['messageType' => $data['messageType'] ?? null, 'key' => $key, 'message' => $m],
        );
    }
}
