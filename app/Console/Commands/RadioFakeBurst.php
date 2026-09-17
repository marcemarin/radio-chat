<?php

namespace App\Console\Commands;

use App\Jobs\IngestMessageJob;
use App\Wa\InboundMessage;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RadioFakeBurst extends Command
{
    protected $signature = 'radio:fake-burst {count=20} {--audio-dir= : si se indica, usa esos audios como mensajes de voz}';

    protected $description = 'Genera una ráfaga de mensajes falsos para probar la Sala sin WhatsApp';

    public function handle(): int
    {
        $texts = [
            'Hola, soy Carlos de Garupá, sin luz desde las 6 de la mañana en todo el barrio',
            'Buen día!! un saludo para mi mamá que cumple años hoy, desde Candelaria',
            '¿Pueden pasar algo de Los Palmeras? gracias',
            'La calle Lavalle al 2000 está destruida, hace un mes que reclamamos y nada',
            'Cómo participo del sorteo de las entradas?',
            'Muy bueno el programa, los escucho todas las mañanas desde Apóstoles',
            'Che qué pasó con el puente? está cortado?',
            'Comparto: PROMO celulares 50% OFF llamá ya al 3764...',
            'Estoy de acuerdo con lo que dijeron del transporte, el 28 pasa cada 40 minutos',
            'Soy Ana de Villa Cabello, quería agradecer al equipo por difundir la colecta',
        ];
        $names = ['Carlos', 'Ana', 'Marta', 'Juan', 'Lucía', 'Pedro', 'Sofía', 'Miguel', null, null];
        $audioDir = $this->option('audio-dir');
        $audios = $audioDir ? glob(rtrim($audioDir, '/').'/*.{ogg,opus,mp3,m4a}', GLOB_BRACE) : [];

        for ($i = 0; $i < (int) $this->argument('count'); $i++) {
            $useAudio = $audios && $i % 2 === 0;
            $in = new InboundMessage(
                provider: 'fake',
                waMessageId: 'FAKE'.Str::upper(Str::random(12)),
                phone: '54937'.random_int(6000000, 6999999),
                pushName: $names[array_rand($names)],
                type: $useAudio ? 'audio' : 'text',
                body: $useAudio ? null : $texts[array_rand($texts)],
                mediaMime: $useAudio ? 'audio/ogg' : null,
                mediaDurationS: $useAudio ? random_int(5, 40) : null,
                sentAt: CarbonImmutable::now()->subSeconds(random_int(0, 120)),
                raw: $useAudio ? ['fake_audio' => $audios[array_rand($audios)]] : [],
            );
            if ($useAudio) {
                cache()->put("fake-audio:{$in->waMessageId}", $in->raw['fake_audio'], now()->addHour());
            }
            IngestMessageJob::dispatch($in)->onQueue('ingest');
        }
        $this->info('Encoladas '.$this->argument('count').' entradas falsas.');

        return self::SUCCESS;
    }
}
