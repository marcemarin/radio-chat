<?php

namespace App\Stt;

/** Para desarrollar sin API keys: devuelve un texto plausible según la duración del archivo. */
class FakeTranscriber implements Transcriber
{
    public function name(): string
    {
        return 'fake';
    }

    public function transcribe(string $absPath, string $mime, string $lang = 'es'): Transcript
    {
        usleep(300_000);
        $samples = [
            'Hola buen día, les habla Juan de Garupá, quería contarles que en el barrio estamos sin luz desde anoche.',
            'Buenas, soy Marta de Villa Cabello, un saludo enorme para todo el equipo, los escucho todas las mañanas.',
            'Che, ¿pueden pasar el tema nuevo de Los Palmeras? Gracias, abrazo desde Candelaria.',
            'Quería denunciar que la calle Lavalle está intransitable, hace dos semanas que nadie viene a arreglar.',
            '¿Sigue el sorteo de las entradas? ¿Cómo participo? Soy Lucía de Posadas.',
        ];

        return new Transcript(
            text: $samples[crc32(basename($absPath)) % count($samples)],
            provider: $this->name(),
            ms: 300,
        );
    }
}
