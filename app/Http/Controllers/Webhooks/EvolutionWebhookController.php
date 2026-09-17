<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\IngestMessageJob;
use App\Wa\EvolutionProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EvolutionWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $event = strtolower(str_replace('_', '.', (string) $request->input('event')));
        $data = $request->input('data', []);

        match ($event) {
            'messages.upsert' => $this->upsert($data),
            'connection.update' => Cache::put('wa:state', $data['state'] ?? 'unknown', now()->addDay()),
            'qrcode.updated' => Cache::put('wa:qr', $data['qrcode']['base64'] ?? null, now()->addMinutes(2)),
            default => null,
        };

        return response()->noContent();
    }

    private function upsert(array $data): void
    {
        // Evolution puede mandar un mensaje o un array de mensajes según versión/evento.
        $items = isset($data['key']) ? [$data] : ($data['messages'] ?? $data);

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $inbound = EvolutionProvider::parseUpsert($item);
            if ($inbound === null) {
                continue;
            }
            IngestMessageJob::dispatch($inbound)->onQueue('ingest');
            Log::info('wa.inbound', ['id' => $inbound->waMessageId, 'type' => $inbound->type]);
        }
    }
}
